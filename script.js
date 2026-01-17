const menuOpenButton = document.querySelector("#menu-open-button");
const menuCloseButton = document.querySelector("#menu-close-button");

menuOpenButton.addEventListener("click", () => {
    // Toggle mobile menu visibility
    document.body.classList.toggle("show-mobile-menu");
});


// Close menu when the close button is clicked
menuCloseButton.addEventListener("click", () =>
    menuOpenButton.click()
);

// Initialize Swiper
const swiper = new Swiper('.slider-wrapper', {
    loop: true,
    GrabCursor : true,

    SpaceBetween: 25,
    // If we need pagination
    pagination: {
        el: '.swiper-pagination',
        Clickable: true,
        DynamicBullets: true,
        },

    // Navigation arrows
    navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
    },

     breakpoints: {
        0: {
            slidesPerView: 1
        },
        768: {
            slidesPerView: 2
        },
        1024: {
            slidesPerView: 3
        }
    }


});
// Add to existing script.js or create new

// Cart functionality
document.addEventListener('DOMContentLoaded', function() {
    // Add to Cart buttons
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            const itemName = this.getAttribute('data-item');
            const itemPrice = this.getAttribute('data-price');
            
            // In real implementation, this would be an AJAX call
            // For now, just show a message
            showCartMessage(`Added ${itemName} to cart!`);
            
            // Update cart count
            updateCartCount(1);
            
            // You would make an AJAX call here:
            // fetch('php/cart_action.php', {
            //     method: 'POST',
            //     body: JSON.stringify({
            //         action: 'add',
            //         item_id: this.getAttribute('data-id'),
            //         quantity: 1
            //     })
            // });
        });
    });
    
    // Update cart count in header
    function updateCartCount(change = 0) {
        const cartCount = document.querySelector('.cart-count');
        if (cartCount) {
            let current = parseInt(cartCount.textContent) || 0;
            current = Math.max(0, current + change);
            cartCount.textContent = current;
        }
    }
    
    // Show cart notification
    function showCartMessage(message) {
        // Create notification
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 100px;
            right: 20px;
            background: var(--success-color);
            color: white;
            padding: 15px 25px;
            border-radius: var(--border-radius-s);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            z-index: 10000;
            animation: slideIn 0.3s ease;
        `;
        notification.innerHTML = `<i class="fas fa-check-circle"></i> ${message}`;
        
        document.body.appendChild(notification);
        
        // Remove after 3 seconds
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
    
    // Add animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
    `;
    document.head.appendChild(style);
});


