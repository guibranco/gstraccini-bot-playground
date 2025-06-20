let allFolders = [];
let allBookmarks = [];
let existingFolders = [];
let existingBookmarks = [];
let folderIdMap = new Map();
let hasExistingJson = false;

document.getElementById("fileInput").addEventListener("change", async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    const data = await parseBookmarks(file);
    const newFolders = data.folders;
    const newBookmarks = data.bookmarks;

    // Apply merge strategy
    const mergeStrategy = document.querySelector('input[name="mergeStrategy"]:checked').value;
    const mergedData = applyMergeStrategy(newFolders, newBookmarks, mergeStrategy);

    allFolders = mergedData.folders;
    allBookmarks = mergedData.bookmarks;

    updateStats();
    renderFolderTree();

    document.getElementById("generateJson").disabled = false;

    // Update file input button text
    const button = document.querySelector('.file-input-button');
    button.innerHTML = `
                <svg class="icon" viewBox="0 0 24 24">
                    <path d="M21,7L9,19L3.5,13.5L4.91,12.09L9,16.17L19.59,5.59L21,7Z" />
                </svg>
                ${file.name}
            `;
    button.style.background = 'linear-gradient(135deg, #10b981, #059669)';
});

document.getElementById("jsonInput").addEventListener("change", async (event) => {
    const file = event.target.files[0];
    if (!file) {
        hasExistingJson = false;
        document.getElementById("mergeOptions").style.display = "none";
        document.getElementById("existingStats").style.display = "none";
        return;
    }

    try {
        const text = await file.text();
        const jsonData = JSON.parse(text);

        existingFolders = jsonData.folders || [];
        existingBookmarks = jsonData.bookmarks || [];
        hasExistingJson = true;

        document.getElementById("mergeOptions").style.display = "block";
        document.getElementById("existingStats").style.display = "flex";
        document.getElementById("existingFolderCount").textContent = existingFolders.length;
        document.getElementById("existingBookmarkCount").textContent = existingBookmarks.length;

        // Update JSON input button text
        const jsonButton = document.querySelector('.json-button');
        jsonButton.innerHTML = `
                    <svg class="icon" viewBox="0 0 24 24">
                        <path d="M21,7L9,19L3.5,13.5L4.91,12.09L9,16.17L19.59,5.59L21,7Z" />
                    </svg>
                    ${file.name}
                `;
        jsonButton.style.background = 'linear-gradient(135deg, #10b981, #059669)';

    } catch (error) {
        alert('Error parsing JSON file: ' + error.message);
    }
});

function applyMergeStrategy(newFolders, newBookmarks, strategy) {
    console.log(`Applying merge strategy: ${strategy}`);
    if (!hasExistingJson || strategy === 'replace') {
        return { folders: newFolders, bookmarks: newBookmarks };
    }

    let resultFolders = [];
    let resultBookmarks = [];

    if (strategy === 'merge') {
        // Merge: combine both, prioritizing new items for conflicts
        const folderMap = new Map();
        const bookmarkMap = new Map();

        // Add existing items first
        existingFolders.forEach(folder => {
            folderMap.set(folder.id, folder);
        });
        existingBookmarks.forEach(bookmark => {
            bookmarkMap.set(bookmark.id, bookmark);
        });

        // Add/update with new items
        newFolders.forEach(folder => {
            folderMap.set(folder.id, folder);
        });
        newBookmarks.forEach(bookmark => {
            bookmarkMap.set(bookmark.id, bookmark);
        });

        resultFolders = Array.from(folderMap.values());
        resultBookmarks = Array.from(bookmarkMap.values());

    } else if (strategy === 'update') {
        // Update: keep existing, add only new items that don't exist
        const existingFolderIds = new Set(existingFolders.map(f => f.id));
        const existingBookmarkIds = new Set(existingBookmarks.map(b => b.id));

        resultFolders = [...existingFolders];
        resultBookmarks = [...existingBookmarks];

        // Add new folders that don't exist
        newFolders.forEach(folder => {
            if (!existingFolderIds.has(folder.id)) {
                resultFolders.push(folder);
            }
        });

        // Add new bookmarks that don't exist
        newBookmarks.forEach(bookmark => {
            if (!existingBookmarkIds.has(bookmark.id)) {
                resultBookmarks.push(bookmark);
            }
        });
    }

    return { folders: resultFolders, bookmarks: resultBookmarks };
}

document.getElementById("generateJson").addEventListener("click", () => {
    const selectedFolders = new Set(
        [...document.querySelectorAll("input[type='checkbox']:checked")].map(checkbox => checkbox.value)
    );

    const filteredFolders = allFolders.filter(folder => selectedFolders.has(folder.id));
    const filteredBookmarks = allBookmarks.filter(bookmark => selectedFolders.has(bookmark.folderId));

    const jsonData = JSON.stringify({ folders: filteredFolders, bookmarks: filteredBookmarks }, null, 2);
    document.getElementById("output").value = jsonData;
    document.getElementById("downloadJson").disabled = false;
});

document.getElementById("downloadJson").addEventListener("click", () => {
    const jsonData = document.getElementById("output").value;
    if (!jsonData) return;

    const blob = new Blob([jsonData], { type: "application/json" });
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = "bookmarks.json";
    link.click();
});

function updateStats() {
    document.getElementById("folderCount").textContent = allFolders.length;
    document.getElementById("bookmarkCount").textContent = allBookmarks.length;
    document.getElementById("stats").style.display = "flex";
}

function renderFolderTree() {
    const folderTree = document.getElementById("folderTree");
    folderTree.innerHTML = "";

    function buildTree(parentId, container) {
        const children = allFolders.filter(folder => folder.parentId === parentId);
        if (!children.length) return;

        children.forEach(folder => {
            const div = document.createElement("div");
            div.classList.add("folder");

            const folderItem = document.createElement("div");
            folderItem.classList.add("folder-item");

            const checkbox = document.createElement("input");
            checkbox.type = "checkbox";
            checkbox.classList.add("folder-checkbox");
            checkbox.value = folder.id;
            checkbox.addEventListener("change", (event) => {
                toggleSubfolders(folder.id, event.target.checked);
            });

            const folderName = document.createElement("span");
            folderName.classList.add("folder-name");
            folderName.textContent = folder.name;

            // Add visual indicator for folders from existing JSON
            if (hasExistingJson && existingFolders.some(f => f.id === folder.id)) {
                const indicator = document.createElement("span");
                indicator.textContent = " (existing)";
                indicator.style.color = "#f59e0b";
                indicator.style.fontSize = "0.8em";
                indicator.style.fontWeight = "500";
                folderName.appendChild(indicator);
            }

            folderItem.appendChild(checkbox);
            folderItem.appendChild(folderName);
            div.appendChild(folderItem);
            container.appendChild(div);

            buildTree(folder.id, div);
        });
    }

    buildTree(null, folderTree);
}

function toggleSubfolders(parentId, isChecked) {
    const childCheckboxes = document.querySelectorAll(`input[type='checkbox']`);

    childCheckboxes.forEach(childCheckbox => {
        if (allFolders.some(folder => folder.id === childCheckbox.value && folder.parentId === parentId)) {
            childCheckbox.checked = isChecked;
            toggleSubfolders(childCheckbox.value, isChecked);
        }
    });
}

async function parseBookmarks(file) {
    const text = await file.text();
    const parser = new DOMParser();
    const doc = parser.parseFromString(text, "text/html");

    let folders = [];
    let bookmarks = [];
    folderIdMap.clear();

    function processNode(node, parentId = null) {
        for (const child of node.children) {
            if (child.tagName === "DT") {
                let firstChild = child.firstElementChild;
                if (!firstChild) continue;

                if (firstChild.tagName === "H3") {
                    let folderId = generateUniqueId(firstChild.textContent);
                    folders.push({ id: folderId, name: firstChild.textContent, parentId });

                    let nextSibling = firstChild.nextElementSibling;
                    if (nextSibling && nextSibling.tagName === "DL") {
                        processNode(nextSibling, folderId);
                    }
                } else if (firstChild.tagName === "A") {
                    bookmarks.push({
                        id: generateUniqueId(firstChild.textContent),
                        title: firstChild.textContent,
                        url: firstChild.getAttribute("HREF"),
                        description: "",
                        thumbnail: "",
                        tags: [],
                        folderId: parentId,
                        favorite: false,
                        dateAdded: new Date(parseInt(firstChild.getAttribute("ADD_DATE") || "0") * 1000).toISOString(),
                    });
                }
            } else if (child.tagName === "DL") {
                processNode(child, parentId);
            }
        }
    }

    function generateUniqueId(name) {
        let baseId = name.toLowerCase().replace(/\s+/g, "-").replace(/[^a-z0-9-]/g, "");
        let uniqueId = baseId;
        let counter = 1;
        while (folderIdMap.has(uniqueId)) {
            uniqueId = `${baseId}-${counter++}`;
        }
        folderIdMap.set(uniqueId, true);
        return uniqueId;
    }

    const rootDL = doc.querySelector("BODY > DL");
    if (rootDL) processNode(rootDL);

    return { folders, bookmarks };
}