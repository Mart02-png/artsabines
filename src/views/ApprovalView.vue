<template>
  <div class="container">
    <h1>List of Clients need approval</h1>


    <DataTable
      :columns="columns"
      :data="tableData"
      class="table table-hover table-striped"
      width="100%"
    >
      <thead>
        <tr>
          <th>ID</th>
          <th>Title</th>
          <th>Email</th>
          <th>Start Date</th>
          <th>End Date</th>
          <th>Action</th>
        </tr>
      </thead>
    </DataTable>
    <button class="btn btn-danger btn-sm" @click="deleteEvent()">Delete</button>
    <!-- Delete -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModal" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title">Delete</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Are you sure you want to delete?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-danger" @click="deleteItemModal()">Delete</button>
          </div>
        </div>
      </div>
    </div>
  </div>




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
  <router-link to="/login" class="nav-link">
    <i class="fas fa-hourglass-half"></i> Calendar
  </router-link>
</li>
<li class="nav-item">
  <router-link to="/approved" class="nav-link">
    <i class="fas fa-check-circle"></i> Approved Patients
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
</template>

<script>
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net-bs5';
import axios from 'axios';
import * as bootstrap from 'bootstrap';

DataTable.use(DataTablesCore);

export default {
  components: {
    DataTable,
  },

  data() {
    return {
      deletemodal: null,
      columns: [
        { data: 'id' },
        { data: 'title' },
        { data: 'email' },
        { data: 'start' },
        { data: 'end' },
        {
          // New action column definition
          data: 'id',
          render: () => {
            return `<button class="btn btn-success btn-sm edit-button">Edit</button>
                    <button class="btn btn-danger btn-sm delete-button">Delete</button>`;
          },
          createdCell: (cell, cellData) => {
              const editButton = cell.querySelector('.edit-button');
              const deleteButton = cell.querySelector('.delete-button');

              editButton.addEventListener('click', () => {
                this.editEvent(cellData);
              });

              deleteButton.addEventListener('click', () => {
                this.deleteEvent(cellData);
              });
            }
        }
      ],
      tableData: [],
    }
  },
  mounted() {
    this.fetchEvents();
    this.deletemodal = new bootstrap.Modal(document.getElementById('deleteModal'), {
      // Optional: specify options here
    });
  },

  methods: {
    fetchEvents() {
      axios.get('http://localhost:8000/api/events')
        .then(response => {
          let eventsFromAPI = response.data.filter(event => event.user === 'clientApproval').map(event => {
            return {
              id: event.id,
              title: event.title,
              email: event.email,
              start: event.start,
              end: event.end,
            };
          });
          this.tableData = [...eventsFromAPI];
        })
        .catch(error => {
          console.error('Error fetching data:', error);
        });
    },
    acceptEvent(id) {
      console.log('Accepting event with ID:', id);
      // Implement your logic here
    },

    editEvent(id) {
      console.log('Edit this ID:', id);
      // Implement your logic here for editing the item with the specified ID
      axios.put(`http://localhost:8000/api/events/${id}`,{
        user: "clientApproved"
      })
        .then(response => {
          console.log("Succesfully Edited: " + id);
          console.log(response.data)
          this.fetchEvents();
        })
        .catch(error => {
          console.error('Error deleting event:', error);
        });

    },


    deleteEvent(id) {
      console.log('Deleting event with ID:', id);
      axios.delete(`http://localhost:8000/api/events/${id}`)
        .then(response => {
          console.log("Succesfully Deleted: " + id);
          console.log(response.data)
          this.fetchEvents();
        })
        .catch(error => {

          console.error('Error deleting event:', error);
        });
        
      // Implement your logic here
    },
    deleteItemModal() {
      console.log('Deleted:');
      this.deleteEvent.hide;
    }
  }
}
</script>

<style>
@import 'bootstrap';
@import 'datatables.net-bs5';
</style>