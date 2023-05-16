// Fetch the API data
fetch("http://localhost/BookManagementSystem/api.php")
  .then(response => response.json())
  .then(data => {
    // Generate HTML table
    const table = document.createElement("table");
    const thead = document.createElement("thead");
    const tbody = document.createElement("tbody");

    // Create table headers
    const headers = ["Title", "Publisher", "Age"];
    const headerRow = document.createElement("tr");
    headers.forEach(headerText => {
      const th = document.createElement("th");
      th.textContent = headerText;
      headerRow.appendChild(th);
    });
    thead.appendChild(headerRow);

    // Create table rows with book data
    data.forEach(book => {
      const row = document.createElement("tr");
      const titleCell = document.createElement("td");
      titleCell.textContent = book.title;
      const publisherCell = document.createElement("td");
      publisherCell.textContent = book.publisher;
      const ageCell = document.createElement("td");
      ageCell.textContent = book.age;

      row.appendChild(titleCell);
      row.appendChild(publisherCell);
      row.appendChild(ageCell);
      tbody.appendChild(row);
    });

    // Append the table to the document
    table.appendChild(thead);
    table.appendChild(tbody);
    document.body.appendChild(table);
  })
  .catch(error => {
    console.error(error);
    // Handle the error, e.g., display an error message
  });
