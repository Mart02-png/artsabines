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
              <i class="fas fa-hourglass-half"></i> Patient Approval
            </router-link>
          </li>
          <li class="nav-item">
            <router-link to="/login" class="nav-link">
              <i class="fas fa-check-circle"></i> Calendar
            </router-link>
          </li>
          <li class="nav-item">
            <router-link to="/profile" class="nav-link">
              <i class="fas fa-user"></i> Profile of Patients
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

    <!-- Patient DataTable -->
    <div style="flex-grow: 1; margin-left: 20px;">
      <h2>List of Approved Patients</h2>
      <table class="table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Last Name</th>
            <th>Email</th>
            <th>Contact</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(patient, index) in approvedPatients" :key="index">
            <td>{{ patient.firstName }}</td>
            <td>{{ patient.lastName }}</td>
            <td>{{ patient.email }}</td>
            <td>{{ patient.contact }}</td>
            <td>
              <button @click="deletePatient(index)" class="btn btn-danger">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      approvedPatients: [
        // Sample data of approved patients
        { firstName: 'John', lastName: 'Doe', email: 'john.doe@example.com', contact: '123-456-7890' },
        { firstName: 'Jane', lastName: 'Smith', email: 'jane.smith@example.com', contact: '987-654-3210' },
        // Add more approved patients as needed
      ]
    };
  },
  methods: {
    logout() {
      // Your logout logic here
    },
    deletePatient(index) {
      // Your delete patient logic here
      // Remove patient from the approvedPatients array based on index
      this.approvedPatients.splice(index, 1);
    }
  }
};
</script>

<style>
/* Add your custom styles here */
</style>
