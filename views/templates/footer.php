</div><!-- End of container -->

    <!-- Advertisement Sidebar -->
    <div class="sidebar">
        <h3>Advertisements</h3>
        <div id="ad-container">
            <!-- Ads will be dynamically loaded here -->
        </div>
    </div>

    <script>
    // Modal Functions
    function openLoginModal() {
        document.getElementById('loginModal').style.display = 'flex';
        document.getElementById('registerModal').style.display = 'none';
    }

    function openRegisterModal() {
        document.getElementById('registerModal').style.display = 'flex';
        document.getElementById('loginModal').style.display = 'none';
    }

    // Close modals when clicking outside
    window.onclick = function(event) {
        if (event.target.className === 'modal') {
            event.target.style.display = 'none';
        }
    }

    // AJAX form submissions
    $(document).ready(function() {
        // Login form submission
        $('#loginForm').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                type: 'POST',
                url: $(this).attr('action'),
                data: $(this).serialize(),
                success: function(response) {
                    const data = JSON.parse(response);
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                }
            });
        });

        // Register form submission
        $('#registerForm').on('submit', function(e) {
            e.preventDefault();
            if ($('#reg_password').val() !== $('#confirm_password').val()) {
                alert('Passwords do not match!');
                return;
            }
            $.ajax({
                type: 'POST',
                url: $(this).attr('action'),
                data: $(this).serialize(),
                success: function(response) {
                    const data = JSON.parse(response);
                    if (data.success) {
                        alert('Registration successful! Please login.');
                        openLoginModal();
                    } else {
                        alert(data.message);
                    }
                }
            });
        });

        // Load advertisements
        function loadAds() {
            $.ajax({
                url: 'controllers/ads/get_active_ads.php',
                type: 'GET',
                success: function(response) {
                    const ads = JSON.parse(response);
                    let adHtml = '';
                    ads.forEach(ad => {
                        adHtml += `
                            <div class="card">
                                <img src="${ad.banner_url}" alt="${ad.title}" style="width: 100%;">
                                <p>${ad.title}</p>
                            </div>
                        `;
                    });
                    $('#ad-container').html(adHtml);
                }
            });
        }

        // Load ads on page load
        loadAds();
        // Refresh ads every 5 minutes
        setInterval(loadAds, 300000);
    });
    </script>
</body>
</html>
