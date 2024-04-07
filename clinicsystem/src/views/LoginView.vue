<template>
    <div class="app">
      <div class="container">
        <div class="d-flex justify-content-center align-items-center" style="min-height: 100vh;">
       
          <div class="col-md-6">
            <div class="card border-0 shadow-lg bg-transparent">
              <div class="card-body p-5">
                <div class="text-center mb-4">
                  <img src="../assets/icon.png" alt="Logo" width="160">
                </div>
                <form @submit.prevent="login">
                  <div class="mb-3">
                    <label for="email" class="form-label visually-hidden">Email</label>
                    <div class="input-group">
                      <span class="input-group-text"><img src="../assets/email.png" width="40" alt="Email"></span>
                      <input type="email" class="form-control" id="email" placeholder="Email" v-model="email" autocomplete="current-email" required>
                    </div>
                  </div>
                  <div class="mb-3">
                    <label for="password" class="form-label visually-hidden">Password</label>
                    <div class="input-group">
                      <span class="input-group-text"><img src="../assets/password.png" width="40" alt="Password"></span>
                      <input type="password" class="form-control" id="password" placeholder="Password" v-model="password" autocomplete="current-password" required>
                    </div>
                  </div>
                  <button type="submit" class="btn btn-primary w-100" :disabled="buttonDisabled"> {{ buttonText }}</button>
                </form>
                <p class="text-center mt-3 text-danger" v-if="errorMessage">{{ errorMessage }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  
  export default {
    data() {
      return {
        email: '',
        password: '',
        errorMessage: '',
        buttonText: 'Login',
        buttonDisabled: false,
      };
    },
    methods: {

      login() {
        this.buttonText = 'Logging in Please wait...';
        this.buttonDisabled = true;
      // Prepare the login credentials
      const credentials = {
        email: this.email,
        password: this.password
      };

      // Make a POST request to the login endpoint
      axios.post('http://localhost:8000/api/auth/login', credentials)
        .then(response => {
          // Handle successful login
          const token = response.data.token;
          console.log('Logged in successfully');
          console.log('Token:', token);
          this.$router.push({ name: 'admin' });
          this.buttonText = 'Login';
          this.buttonDisabled = false;
          // Perform any additional actions after login, such as storing the token or redirecting the user
        })
        .catch(error => {
          // Handle error
          console.error('Login error:', error.response.data.message);
          this.buttonText = 'Login';
          this.buttonDisabled = false;
        });
    }

      // login() {
      //   this.buttonText = 'Logging in Please wait...';
      //   this.buttonDisabled = true;

      //   // Perform login request using Axios
      //   axios.post('http://localhost:8000/api/auth/login', {
      //     email: this.email,
      //     password: this.password
      //   })
      //   .then(response => {
      //     // Handle successful login
      //     console.log(response.data); // For debugging
      //     // You can store the token in local storage or cookies
      //     localStorage.setItem('token', response.data.token);
      //     // Redirect to another page or perform any other action
      //     // Route to the admin view
      //     localStorage.setItem('email', this.email);
      //     this.$router.push({ name: 'admin' });
      //     this.buttonText = 'Login';
      //     this.buttonDisabled = false;
      //   })
      //   .catch(error => {
      //     // Handle login error
      //     console.error(error.response.data); // For debugging
      //     this.errorMessage = error.response.data.error || 'Login failed. Please try again.';
      //     this.errorMessage = error.response.data.message;
      //     this.buttonText = 'Login';
      //     this.buttonDisabled = false;
      //   });
      // }
    }
  };
  </script>
  
  <style>
  
  #app {
  background-image: url("../assets/bgLogin.jpg");
  background-size: cover;
  background-position: center;
  height: 100vh;
}
  .visually-hidden {
    position: absolute !important;
    width: 1px !important;
    height: 1px !important;
    padding: 0 !important;
    margin: -1px !important;
    overflow: hidden !important;
    clip: rect(0, 0, 0, 0) !important;
    white-space: nowrap !important;
    border: 0 !important;
  }

  /* Additional style for making card border transparent */
.card {
  border-color: rgba(255, 255, 255, 0.7);
  border-radius: 20px;
}

.card-body {
  background-color: rgba(255, 255, 255, 0.7); /* Optional: If you want the card body to be semi-transparent too */
  border-radius: 20px;
}
  </style>
  