<template>
    <div class='admin-app'>
  
      <!-- Admin Sidebar -->
      <div class='demo-app-sidebar' style="position: sticky; top: 0; display: flex; flex-direction: column; justify-content: space-between; width: 250px; border: 2px solid #000;">
        <div>
          <div class="text-center">
            <img src="../assets/icon.png" alt="Ears Nose and Throat" style="width: 140px; height: auto; " class="img-fluid">
          </div>
          <div style="margin-top: 25px;">
            <h5 style="font-weight: bold; font-size: 1.5em; color: #333; text-transform: uppercase; letter-spacing: 1px;"> Gentle Care Clinic</h5>
          </div>
        </div>
        <div>
          <ul class="nav flex-column">
            <li class="nav-item">
    <router-link to="/approved" class="nav-link">
      <i class="fas fa-hourglass-half"></i>Patient Approval
    </router-link>
  </li>
  <li class="nav-item">
    <router-link to="/approved" class="nav-link">
      <i class="fas fa-check-circle"></i> Approved Patients
    </router-link>
  </li>
  
  <li class="nav-item">
    <router-link to="/login" class="nav-link">
      <i class="fas fa-user"></i> Calendar
    </router-link>
  </li>
  
          </ul>
        </div>
        
        <div style="text-align: center; margin-top: 20px;">
          <button @click="logout" style="background: none; border: none;">
            <i class="fas fa-sign-out-alt" style="font-size: 24px; color: #333;"></i>
          </button>
        </div>
      </div>
      </div>
      </template>
