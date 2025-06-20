function convertHTMLtoJSX() {
    const inputHTML = document.getElementById("htmlInput").value;
    const parser = new DOMParser();
    const doc = parser.parseFromString(inputHTML, "text/html");
    const expRows = doc.querySelectorAll(".exp-row");

    let companies = {};

    expRows.forEach(row => {
        const nameElement = row.querySelector(".name");
        const periodElement = row.querySelector(".year");
        const jobElement = row.querySelector(".job");
        const descriptionElement = row.querySelector("p");
        const achievementsElements = row.querySelectorAll("ul li");

        if (!nameElement || !jobElement || !periodElement) return;

        // Extract company name, flag, and additional description
        let companyText = nameElement.childNodes[0].nodeValue.trim();
        let companyDescription = nameElement.querySelector("span")?.textContent.trim() || "";
        let flagElement = nameElement.querySelector("img");
        let flagURL = flagElement ? flagElement.src : "";

        let companyKey = companyText.toLowerCase().replace(/\s+/g, "_");

        // Define company entry if not exists
        if (!companies[companyKey]) {
            companies[companyKey] = {
                company: companyText,
                logo: `https://guibranco.github.io/images/experience/${companyText.replace(/\s+/g, "")}.png`,
                location: flagElement ? flagElement.title : "",
                description: companyDescription,
                period: periodElement.textContent.trim(),
                flag: flagURL,
                roles: []
            };
        } else {
            // Update the overall period if new role has an earlier start
            companies[companyKey].period = `${periodElement.textContent.trim()} - Present`;
        }

        // Extract role details
        let role = {
            title: jobElement.textContent.trim(),
            period: periodElement.textContent.trim(),
            description: descriptionElement?.textContent.trim() || "",
            achievements: [...achievementsElements].map(li => li.textContent.trim())
        };

        // Add the role to the company
        companies[companyKey].roles.push(role);
    });

    // Convert to JSX format
    let outputJSX = Object.values(companies).map(company => JSON.stringify(company, null, 2)).join(",\n\n");

    document.getElementById("jsxOutput").value = outputJSX;
}