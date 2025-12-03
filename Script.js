document.addEventListener("DOMContentLoaded", () => {

    const userMenu = document.getElementById("userMenu");
    let userName = "Augustas"; // simuliacija

    userMenu.innerHTML = `
        <div class="dropdown">
            <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                ${userName}
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#">Mano profilis</a></li>
                <li><a class="dropdown-item" href="#">Mano vertinimai</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="#">Atsijungti</a></li>
            </ul>
        </div>
    `;
});
