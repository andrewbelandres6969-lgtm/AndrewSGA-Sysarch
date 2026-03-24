function showHome() {
    const home = document.getElementById("homeSection");
    const reg = document.getElementById("registerSection");
    const log = document.getElementById("loginSection");

    if (home) home.classList.remove("hidden");
    if (reg) reg.classList.add("hidden");
    if (log) log.classList.add("hidden");
}

function showRegister() {
    const home = document.getElementById("homeSection");
    const reg = document.getElementById("registerSection");
    const log = document.getElementById("loginSection");

    if (home) home.classList.add("hidden");
    if (reg) reg.classList.remove("hidden");
    if (log) log.classList.add("hidden");
}

function showLogin() {
    const home = document.getElementById("homeSection");
    const reg = document.getElementById("registerSection");
    const log = document.getElementById("loginSection");

    if (home) home.classList.add("hidden");
    if (reg) reg.classList.add("hidden");
    if (log) log.classList.remove("hidden");
}

const countdownElement = document.getElementById("countdown");

if (countdownElement) {
    const endTime = new Date(countdownElement.dataset.end).getTime();

    function updateCountdown() {
        const now = new Date().getTime();
        const distance = endTime - now;

        if (distance <= 0) {
            countdownElement.innerHTML = "Session expired";
            return;
        }

        const hours = Math.floor(distance / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        countdownElement.innerHTML = hours + "h " + minutes + "m " + seconds + "s";
    }

    updateCountdown();
    setInterval(updateCountdown, 1000);
}