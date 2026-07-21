/**
 * 通用 Cloudflare API CORS 代理
 * ------------------------------------------------------------
 * 把你在瀏覽器裡呼叫的路徑，原封不動轉發到 https://api.cloudflare.com，
 * 並加上 CORS header，讓瀏覽器端的網頁工具可以直接打 Cloudflare API。
 *
 * 用法：把你原本要打的 URL
 *   https://api.cloudflare.com/client/v4/xxx
 * 換成
 *   https://<你的-worker>.workers.dev/client/v4/xxx
 * 其餘 method / headers（含 Authorization）/ body 完全照轉，回應也照轉。
 *
 * 這支 Worker 沒有寫死任何 Cloudflare token 或帳號資訊，
 * 你的 token 全程只在「瀏覽器 → 你自己的 Worker → Cloudflare API」之間傳遞，
 * 不會經過任何第三方。之後任何要打 Cloudflare API 的網頁小工具都能重複使用這支 Worker。
 *
 * 【可選】想多一層保護，避免別人知道你的 Worker 網址就能拿去用：
 *   1. 在 Cloudflare dashboard 開這個 Worker 的 Settings → Variables and Secrets
 *   2. 新增一個 Secret，名稱 PROXY_KEY，值自己設一串隨機字串
 *   3. 之後瀏覽器端的請求要多帶一個 header： X-Proxy-Key: 你設的那串字串
 *   （不設定 PROXY_KEY 的話，這一層檢查會自動略過）
 */

export interface Env {
  PROXY_KEY?: string;
}

const UPSTREAM = "https://api.cloudflare.com";

function corsHeaders(request: Request): Record<string, string> {
  const origin = request.headers.get("Origin") || "*";
  return {
    "Access-Control-Allow-Origin": origin,
    "Access-Control-Allow-Methods": "GET,POST,PUT,PATCH,DELETE,OPTIONS",
    "Access-Control-Allow-Headers":
      "Authorization, Content-Type, X-Proxy-Key, X-Auth-Email, X-Auth-Key",
    "Access-Control-Expose-Headers": "*",
    "Access-Control-Max-Age": "86400",
    "Vary": "Origin",
  };
}

export default {
  async fetch(request: Request, env: Env): Promise<Response> {
    const cors = corsHeaders(request);

    if (request.method === "OPTIONS") {
      return new Response(null, { headers: cors });
    }

    if (env.PROXY_KEY) {
      const provided = request.headers.get("X-Proxy-Key");
      if (provided !== env.PROXY_KEY) {
        return new Response("Forbidden: missing/invalid X-Proxy-Key", {
          status: 403,
          headers: cors,
        });
      }
    }

    const url = new URL(request.url);
    const targetUrl = UPSTREAM + url.pathname + url.search;

    const upstreamHeaders = new Headers(request.headers);
    upstreamHeaders.delete("host");
    upstreamHeaders.delete("x-proxy-key");
    upstreamHeaders.delete("origin");
    upstreamHeaders.delete("referer");

    const init: RequestInit = {
      method: request.method,
      headers: upstreamHeaders,
      body: ["GET", "HEAD"].includes(request.method) ? undefined : request.body,
      redirect: "manual",
    };

    let upstreamResp: Response;
    try {
      upstreamResp = await fetch(targetUrl, init);
    } catch (err) {
      const message = err instanceof Error ? err.message : String(err);
      return new Response("Proxy fetch failed: " + message, {
        status: 502,
        headers: cors,
      });
    }

    const respHeaders = new Headers(upstreamResp.headers);
    for (const [k, v] of Object.entries(cors)) respHeaders.set(k, v);
    respHeaders.delete("content-security-policy");

    return new Response(upstreamResp.body, {
      status: upstreamResp.status,
      statusText: upstreamResp.statusText,
      headers: respHeaders,
    });
  },
} satisfies ExportedHandler<Env>;
