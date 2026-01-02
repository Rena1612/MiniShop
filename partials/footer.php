<?php
/*
 * Footer Partial - Reusable Footer Component
 * Included at the bottom of every page
 * Features: Site info, Quick links, Contact, Copyright
 */
?>
    </main>
    <!-- Main Content Area Ends -->
    
    <!-- Footer Section -->
    <footer class="main-footer">
        <div class="container">
            <div class="footer-content">
                <!-- About Section -->
                <div class="footer-section">
                    <h3>About MiniShop</h3>
                    <p>Your trusted online store for quality products at great prices. We offer a wide selection of electronics, clothing, books, and more.</p>
                    <p class="footer-tagline">🛒 Shop Smart, Shop MiniShop!</p>
                </div>
                
                <!-- Quick Links Section -->
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="/minishop/index.php">🏠 Home</a></li>
                        <li><a href="/minishop/shop.php">🛍️ Shop All Products</a></li>
                        <li><a href="/minishop/cart.php">🛒 Shopping Cart</a></li>
                        <li><a href="/minishop/shop.php?category=1">💻 Electronics</a></li>
                        <li><a href="/minishop/shop.php?category=2">👕 Clothing</a></li>
                    </ul>
                </div>
                
                <!-- Contact Section -->
                <div class="footer-section">
                    <h3>Contact Us</h3>
                    <ul class="footer-contact">
                        <li>📧 <strong>Email:</strong> info@myshop.com</li>
                        <li>📞 <strong>Phone:</strong> (123) 456-7890</li>
                        <li>📍 <strong>Address:</strong> 123 Shop Street, City, ST 12345</li>
                        <li>🕒 <strong>Hours:</strong> Mon-Fri: 9AM-6PM</li>
                    </ul>
                </div>
                
                <!-- Customer Service Section -->
                <div class="footer-section">
                    <h3>Customer Service</h3>
                    <ul class="footer-links">
                        <li><a href="#"> Help & FAQs</a></li>
                        <li><a href="#"> Shipping Info</a></li>
                        <li><a href="#">↩ Returns Policy</a></li>
                        <li><a href="#"> Privacy Policy</a></li>
                        <li><a href="#"> Terms of Service</a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Footer Bottom Bar -->
            <div class="footer-bottom">
                <div class="footer-bottom-content">
                    <p>&copy; <?php echo date('Y'); ?> MiniShop. All rights reserved.</p>
                    <p class="footer-credits">
                        Built with using PHP & MySQL | 
                        <span id="current-time"></span>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Optional: Display current time dynamically -->
    <script>
        // Update current time in footer
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', { 
                hour: '2-digit', 
                minute: '2-digit',
                second: '2-digit'
            });
            const timeElement = document.getElementById('current-time');
            if (timeElement) {
                timeElement.textContent = 'Server Time: ' + timeString;
            }
        }
        
        // Update time every second
        updateTime();
        setInterval(updateTime, 1000);
    </script>
</body>
</html>