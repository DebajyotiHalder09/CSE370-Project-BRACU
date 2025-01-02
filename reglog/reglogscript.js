document.addEventListener("DOMContentLoaded", () => {
    const loginBox = document.getElementById("loginBox");
    const registerBox = document.getElementById("registerBox");
    const toggleButtons = document.querySelectorAll(".toggle-button");

    toggleButtons.forEach((button) => {
        button.addEventListener("click", () => {
            if (loginBox.classList.contains("d-none")) {
                registerBox.classList.add("fade-out");
                setTimeout(() => {
                    registerBox.classList.add("d-none");
                    registerBox.classList.remove("fade-out");
                    loginBox.classList.remove("d-none");
                    loginBox.classList.add("fade-in");
                    setTimeout(() => loginBox.classList.remove("fade-in"), 500);
                }, 500);
            } else {
                loginBox.classList.add("fade-out");
                setTimeout(() => {
                    loginBox.classList.add("d-none");
                    loginBox.classList.remove("fade-out");
                    registerBox.classList.remove("d-none");
                    registerBox.classList.add("fade-in");
                    setTimeout(() => registerBox.classList.remove("fade-in"), 500);
                }, 500);
            }
        });
    });
});
