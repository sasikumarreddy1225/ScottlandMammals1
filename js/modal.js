document.addEventListener("DOMContentLoaded", () => {
    const clickableElements = document.querySelectorAll(".card-img, .details-header");

    clickableElements.forEach(el => {
        el.addEventListener("click", () => {
            let imageUrl = "";

            if (el.tagName === "IMG") {
                imageUrl = el.src;
            } else {
                const bg = window.getComputedStyle(el).backgroundImage;
                
                const urlMatch = bg.match(/url\(["']?([^"']+)["']?\)/);
                
                if (urlMatch && urlMatch[1]) {
                    imageUrl = urlMatch[1];
                }
            }

            if (!imageUrl) return;

            let modal = document.createElement("div");
            Object.assign(modal.style, {
                position: "fixed",
                top: 0,
                left: 0,
                width: "100%",
                height: "100%",
                background: "rgba(0,0,0,0.9)",
                display: "flex",
                justifyContent: "center",
                alignItems: "center",
                zIndex: "9999",
                cursor: "zoom-out"
            });

            let bigImg = document.createElement("img");
            bigImg.src = imageUrl;
            Object.assign(bigImg.style, {
                maxWidth: "90%",
                maxHeight: "90%",
                borderRadius: "8px",
                border: "5px solid white",
                boxShadow: "0 0 30px rgba(0,0,0,0.5)"
            });

            modal.appendChild(bigImg);
            document.body.appendChild(modal);
            modal.onclick = () => modal.remove();
        });
    });
});