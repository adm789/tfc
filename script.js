let display = document.getElementById('display');
let currentInput = "";

function press(value) {
    currentInput += value;
    display.innerText = currentInput;
}

function clearDisplay() {
    currentInput = "";
    display.innerText = "0";
}

function calculate() {
    try {
        currentInput = eval(currentInput).toString();
        display.innerText = currentInput;
    } catch (e) {
        display.innerText = "Error";
    }
}