// =====================================
// STUDENTHUB EVENTS
// FETCH + SEARCH + FILTER + SORT
// =====================================

let events = [];

let currentPage = 1;

const eventsPerPage = 6;


// Get elements

const eventsList =
    document.getElementById("eventsList");

const searchInput =
    document.getElementById("searchInput");

const categoryFilter =
    document.getElementById("categoryFilter");

const sortSelect =
    document.getElementById("sortSelect");

const loading =
    document.getElementById("loading");

const error =
    document.getElementById("error");

const pagination =
    document.getElementById("pagination");


// =====================================
// FETCH JSON
// =====================================

fetch("./events.json")

    .then(function(response) {

        if (!response.ok) {

            throw new Error(
                "events.json not found"
            );

        }

        return response.json();

    })

    .then(function(data) {

        console.log("JSON loaded:", data);

        events = data;

        loading.style.display = "none";

        displayEvents();

    })

    .catch(function(err) {

        console.log("Fetch Error:", err);

        loading.style.display = "none";

        error.textContent =
            "Unable to load events. Check events.json.";

    });


// =====================================
// DISPLAY EVENTS
// =====================================

function displayEvents() {

    let result = events;


    // SEARCH

    const search =
        searchInput.value.toLowerCase();

    result = result.filter(function(event) {

        return event.title
            .toLowerCase()
            .includes(search);

    });


    // FILTER

    if (categoryFilter.value !== "all") {

        result = result.filter(function(event) {

            return event.category ===
                   categoryFilter.value;

        });

    }


    // SORT

    if (sortSelect.value === "az") {

        result.sort(function(a, b) {

            return a.title.localeCompare(
                b.title
            );

        });

    }


    if (sortSelect.value === "za") {

        result.sort(function(a, b) {

            return b.title.localeCompare(
                a.title
            );

        });

    }


    if (sortSelect.value === "date") {

        result.sort(function(a, b) {

            return new Date(a.date) -
                   new Date(b.date);

        });

    }


    // PAGINATION

    const start =
        (currentPage - 1) * eventsPerPage;

    const end =
        start + eventsPerPage;

    const pageEvents =
        result.slice(start, end);


    // CLEAR

    eventsList.innerHTML = "";


    // NO RESULT

    if (pageEvents.length === 0) {

        eventsList.innerHTML =
            "<p>No events found.</p>";

        pagination.innerHTML = "";

        return;

    }


    // DISPLAY

    pageEvents.forEach(function(event) {

        const card =
            document.createElement("div");

        card.className = "event-card";


        card.innerHTML =

            "<h3>" +
            event.title +
            "</h3>" +

            "<p><strong>Category:</strong> " +
            event.category +
            "</p>" +

            "<p><strong>Date:</strong> " +
            event.date +
            "</p>" +

            "<p><strong>Location:</strong> " +
            event.location +
            "</p>";


        eventsList.appendChild(card);

    });


    createPagination(result.length);

}


// =====================================
// PAGINATION
// =====================================

function createPagination(totalEvents) {

    pagination.innerHTML = "";


    const totalPages =
        Math.ceil(
            totalEvents / eventsPerPage
        );


    for (
        let i = 1;
        i <= totalPages;
        i++
    ) {

        const button =
            document.createElement("button");

        button.textContent = i;


        if (i === currentPage) {

            button.classList.add("active");

        }


        button.addEventListener(
            "click",
            function() {

                currentPage = i;

                displayEvents();

            }
        );


        pagination.appendChild(button);

    }

}


// =====================================
// SEARCH
// =====================================

searchInput.addEventListener(
    "input",
    function() {

        currentPage = 1;

        displayEvents();

    }
);


// =====================================
// FILTER
// =====================================

categoryFilter.addEventListener(
    "change",
    function() {

        currentPage = 1;

        displayEvents();

    }
);


// =====================================
// SORT
// =====================================

sortSelect.addEventListener(
    "change",
    function() {

        currentPage = 1;

        displayEvents();

    }
);