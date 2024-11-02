/* this enables to turn this script off without reloading whole page; just declare disable as true*/
let disable = false;

const listDate = (date) => {
  if (disable) {
    return;
  }
  console.log("listing: ", date);
  document.querySelector("#statistics-investment").value = "funds";
  document.querySelector(".horizontal-filter-date.js-statistics-filter-date.form-control.input").value = date;
  document.querySelector("#statistics-date").value = date;

  document.querySelector(".button.button-shadow.js-statistics-search-submit").click();

  setTimeout(() => {
    if (document.querySelector(".js-statistics-results-count").innerText == "0") {
      console.log("no results");
      listDate(decrementDate(date));
    } else {
      if (document.querySelector(".primary.load-more.js-loadmore-statistics") && !document.querySelector(".primary.load-more.js-loadmore-statistics.is-hidden")) {
        loadMore(date);
      } else {
        console.log("end");
        getTable(date);
      }
    }
  }, 1500);
};

/* load all content of the page, timeout to emulute user behaviour */
const loadMore = (date) => {
  console.log("load more");
  setTimeout(() => {
    document.querySelector(".primary.load-more.js-loadmore-statistics").click();
    setTimeout(() => {
      if (document.querySelector(".primary.load-more.js-loadmore-statistics") && !document.querySelector(".primary.load-more.js-loadmore-statistics.is-hidden")) {
        loadMore(date);
      } else {
        console.log("end");
        getTable(date);
      }
    }, 1500);
  }, 1500);
};

/* pick data and send as json to the API */
const getTable = (date) => {
  console.log("get table");
  const table = document.querySelector(".stock-table.large-table.table-container.js-swipe-icon tbody");
  const rows = table.querySelectorAll("tr");

  let data = {
    date: date,
    items: [],
  };

  rows.forEach((row) => {
    let item = {};
    item.jmeno = row.querySelector("a").innerText;
    item.isin = row.querySelector(".isin").innerText;
    item.kurz = row.querySelectorAll("td")[1].innerText;
    item.zmena = row.querySelectorAll("td")[2].innerText;
    item.pocet = row.querySelectorAll("td")[3].innerText;
    item.objem = row.querySelectorAll("td")[4].innerText;

    data.items.push(item);
  });

  console.log(data);

  fetch("https://diplomka.grundza.cz/burza-praha?token=**********", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(data),
  })
    .then((response) => response.json())
    .then((data) => {
      console.log("Success:", data);
    })
    .catch((error) => {
      console.error("Error:", error);
    });

  listDate(decrementDate(date));
};

/**
 * this methods decrements date by one day
 * expects date in a format YYYY-MM-DD
 * @param {string} date
 */
const decrementDate = (date) => {
  let newDate = new Date(date);
  newDate.setDate(newDate.getDate() - 1);
  return newDate.toISOString().split("T")[0];
};
