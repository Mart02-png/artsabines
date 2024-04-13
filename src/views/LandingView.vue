    <template>
        <div class='demo-app'>
        <div class='demo-app-sidebar' style="position: sticky; top: 0; display: flex; flex-direction: column; justify-content: space-between; width: 250px; border: 2px solid #000;">
            <div>
            <div class="text-center">
                <img src="../assets/icon.png" alt="Ears Nose and Throat" style="width: 140px; height: auto; " class="img-fluid">
            </div>
            <div style="margin-top: 25px;">
                <h5 style="font-weight: bold; font-size: 1.5em; color: #333; text-transform: uppercase; letter-spacing: 1px;"> Gentle Care Clinic</h5>
            </div>
            </div>
            <div style="margin-top: 45px;">
            <ul style="list-style-type: none; padding: 0;">
                <li style="margin-bottom: 10px;">
                <router-link to="/" class="nav-link">
                    <h6 style="font-weight: bold; font-size: 1.3em; ">Navigation Link</h6>
                    <i class="fas fa-calendar"></i> Calendar
                </router-link>
                </li>
                <li style="margin-bottom: 10px;">
                <router-link to="/forum" class="nav-link">
               
                </router-link>
                </li>
                <li style="margin-bottom: 10px;">
                <router-link to="/community" class="nav-link">
                
                </router-link>
                </li>
            </ul>
            </div>
            <div style="margin-top: 10px;">
            <h6 style="font-weight: bold;font-size: 1.3em; ">Social Media Links</h6>
            <ul style="list-style-type: none; padding: 0;">
                <li style="margin-bottom: 10px;">
                <a href="https://www.facebook.com" class="social-link">
                    <i class="fab fa-facebook"></i> Facebook
                </a>
                </li>
                <li style="margin-bottom: 10px;">
                <a href="https://t.me" class="social-link">
                    <i class="fab fa-telegram"></i> Telegram
                </a>
                </li>
                <li style="margin-bottom: 10px;">
                <a href="https://twitter.com" class="social-link">
                    <i class="fab fa-twitter"></i> Twitter
                </a>
                </li>
            </ul>
            </div>
            <!--for admin-->
            
            <!--for admin-->
        </div>
        
        <div class="container">
    <h2 class="clinic-name">GENTLE CARE CLINIC</h2>
   
    <div class="about-section">
      <div class="image-container">
        <img src="../assets/cli.jpg" alt="Clinic Image" class="clinic-image">
      </div>
    </div>
    <div class="mission-vision-section">
      <div class="card mission-card">
        <h3>Mission</h3>
        <p>Our mission is to provide compassionate and high-quality healthcare services to our community.</p>
      </div>
      <div class="card vision-card">
        <h3>Vision</h3>
        <p>Our vision is to be the leading healthcare provider, delivering exceptional patient care and innovative treatments.</p>
      </div>
    </div>
    <div class="footer-content">
      <p>&copy; 2024 Gentle Care Clinic. All rights reserved.</p>
    </div>
  </div>
  
</div>
  




        <!-- Add more content or sections as needed -->
            

        
    </template>

    <style src="@/styles/LandingViewStyles.css">



    </style>
