function validateForm() {
    let name = document.getElementById("name").value;
    let species = document.getElementById("species").value;
    let details = document.getElementById("details").value;

    if (!name || !species || !details) {
        alert("All fields are required");
        return false;
    }
    return true;
}