document.addEventListener("DOMContentLoaded", function () {

    const body = document.body;
    const header = document.querySelector("header");
    const nav = document.querySelector("nav");

    // =====================================
    // 1. SAVED THEME RESTORE
    // =====================================

    const savedTheme =
        localStorage.getItem("studentHubTheme");

    if (savedTheme === "dark") {
        body.classList.add("dark-theme");
    }

    // =====================================
    // 2. NOTIFICATION BANNER
    // =====================================

    const banner = document.createElement("div");

    banner.className = "notification-banner";

    banner.innerHTML = `
        <span>
            Welcome to students hub portal
        </span>

        <button
            class="banner-close"
            aria-label="Close notification">
            &times;
        </button>
    `;

    body.prepend(banner);

    const bannerClose =
        banner.querySelector(".banner-close");

    bannerClose.addEventListener("click", function () {
        banner.remove();
    });

    // =====================================
    // 3. HAMBURGER MENU
    // =====================================

    if (header && nav) {

        nav.id = "main-navigation";

        const menuButton =
            document.createElement("button");

        menuButton.className = "menu-toggle";
        menuButton.textContent = "☰ Menu";

        menuButton.setAttribute(
            "aria-controls",
            "main-navigation"
        );

        menuButton.setAttribute(
            "aria-expanded",
            "false"
        );

        header.insertBefore(menuButton, nav);

        menuButton.addEventListener(
            "click",
            function () {

                nav.classList.toggle("nav-open");

                const menuOpen =
                    nav.classList.contains("nav-open");

                menuButton.setAttribute(
                    "aria-expanded",
                    menuOpen
                );

                if (menuOpen) {
                    menuButton.textContent = "✕ Close";
                } else {
                    menuButton.textContent = "☰ Menu";
                }
            }
        );

        // =================================
        // 4. LIGHT / DARK MODE
        // =================================

        const themeButton =
            document.createElement("button");

        themeButton.className = "theme-toggle";

        header.appendChild(themeButton);

        function updateThemeButton() {

            const darkMode =
                body.classList.contains("dark-theme");

            if (darkMode) {
                themeButton.textContent = "☀ Light Mode";
            } else {
                themeButton.textContent = "🌙 Dark Mode";
            }
        }

        updateThemeButton();

        themeButton.addEventListener(
            "click",
            function () {

                body.classList.toggle("dark-theme");

                const darkMode =
                    body.classList.contains("dark-theme");

                if (darkMode) {

                    localStorage.setItem(
                        "studentHubTheme",
                        "dark"
                    );

                } else {

                    localStorage.setItem(
                        "studentHubTheme",
                        "light"
                    );
                }

                updateThemeButton();
            }
        );
    }

    // =====================================
    // CURRENT PAGE NAME
    // =====================================

    const currentPage =
        window.location.pathname
            .split("/")
            .pop()
            .toLowerCase();

    // =====================================
    // 6. COLLAPSIBLE FAQ
    // =====================================

    function createFAQ() {

        const footer =
            document.querySelector("footer");

        if (!footer) {
            return;
        }

        const faq =
            document.createElement("section");

        faq.className = "faq-section";

        faq.innerHTML = `
            <h2>
                Frequently Asked Questions
            </h2>

            <div class="faq-item">

                <button
                    class="faq-question"
                    aria-expanded="false">

                    How can I view notes?

                    <span>+</span>
                </button>

                <div class="faq-answer">

                    <p>
                        Open the Notes page from
                        the navigation menu.
                    </p>

                </div>
            </div>

            <div class="faq-item">

                <button
                    class="faq-question"
                    aria-expanded="false">

                    Where can I check assignments?

                    <span>+</span>
                </button>

                <div class="faq-answer">

                    <p>
                        Open the Assignments page
                        to check deadlines and status.
                    </p>

                </div>
            </div>

            <div class="faq-item">

                <button
                    class="faq-question"
                    aria-expanded="false">

                    Is my theme remembered?

                    <span>+</span>
                </button>

                <div class="faq-answer">

                    <p>
                        Yes. The theme is saved
                        using localStorage.
                    </p>

                </div>
            </div>
        `;

        body.insertBefore(faq, footer);

        const questions =
            faq.querySelectorAll(".faq-question");

        questions.forEach(function (question) {

            question.addEventListener(
                "click",
                function () {

                    const open =
                        question.getAttribute(
                            "aria-expanded"
                        ) === "true";

                    question.setAttribute(
                        "aria-expanded",
                        String(!open)
                    );

                    question.parentElement
                        .classList.toggle(
                            "open",
                            !open
                        );

                    const symbol =
                        question.querySelector("span");

                    if (open) {
                        symbol.textContent = "+";
                    } else {
                        symbol.textContent = "−";
                    }
                }
            );
        });
    }

    // =====================================
    // 7. REGISTER SUCCESS MESSAGE
    // =====================================

    if (currentPage === "register.html") {

        const registerForm =
            document.querySelector("form");

        if (registerForm) {

            registerForm.addEventListener(
                "submit",
                function (event) {

                    event.preventDefault();

                    alert(
                        "Registration completed successfully!"
                    );

                    registerForm.reset();
                }
            );
        }
    }

    // =====================================
    // 8. FEEDBACK SUCCESS MESSAGE
    // =====================================

    if (currentPage === "feedback.html") {

        const feedbackForm =
            document.querySelector("form");

        if (feedbackForm) {

            feedbackForm.addEventListener(
                "submit",
                function (event) {

                    event.preventDefault();

                    alert(
                        "Feedback submitted successfully!"
                    );

                    feedbackForm.reset();
                }
            );
        }
    }

    // =====================================
    // 9. LOGIN SUCCESS MESSAGE
    // =====================================

    if (currentPage === "login.html") {

        const loginButton =
            document.querySelector(
                'button[type="button"]'
            );

        if (loginButton) {

            loginButton.addEventListener(
                "click",
                function (event) {

                    event.preventDefault();

                    alert(
                        "Login successful!"
                    );

                    window.location.href =
                        "dashboard.html";
                }
            );
        }
    }

    // =====================================
    // 10. ASSIGNMENT SUCCESS MESSAGE
    // =====================================

    if (currentPage === "assignments.html") {

        const assignmentButton =
            document.querySelector(
                "section button"
            );

        if (assignmentButton) {

            assignmentButton.addEventListener(
                "click",
                function () {

                    alert(
                        "Assignment submitted successfully!"
                    );
                }
            );
        }
    }

    // =====================================
    // 11. NOTES DOWNLOAD MESSAGE
    // =====================================

    if (currentPage === "notes.html") {

        const downloadButtons =
            document.querySelectorAll(
                "table button"
            );

        downloadButtons.forEach(
            function (button) {

                button.addEventListener(
                    "click",
                    function () {

                        alert(
                            "Notes downloaded successfully!"
                        );
                    }
                );
            }
        );
    }

    // =====================================
    // 12. PROFILE UPDATE MESSAGE
    // =====================================

    if (currentPage === "profile.html") {

        const editButton =
            document.querySelector(
                "section button"
            );

        if (editButton) {

            editButton.addEventListener(
                "click",
                function () {

                    alert(
                        "Profile updated successfully!"
                    );
                }
            );
        }
    }

});