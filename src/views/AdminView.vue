<!-- AdminView.vue -->

<script>
// require('@fullcalendar/core/main.min.css')

import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'
import listPlugin from '@fullcalendar/list'

import axios from 'axios';
// import { INITIAL_EVENTS, createEventId } from './event-utils'
import bootstrap5Plugin from '@fullcalendar/bootstrap5'
import * as bootstrap from 'bootstrap';

let eventGuid = 0
export function createEventId() {
  return String(eventGuid++)
}

function formatDatetime(datetimeStr) {
  return datetimeStr.replace('T', ' ').replace(/\+.*$/, '');
}

export default {
  components: {
    FullCalendar // make the <FullCalendar> tag available
  },

  data: function () {
    return {
      selectedStartTime: '',
      selectedEndTime: '',
      inputPlaceholder: 'Schedule Name',
      // showInputsNumAplha: false,
      buttonText: 'Save changes',
      modalTitle: 'Create Schedule',
      buttonDisabled: false,
      modal: null,
      deletemodal: null,
      savingConfirmation: null,


      nameRegex: /^[a-zA-Z\s]*$/,
      numberRegex: /^\d+$/,
      adminEmail: '',
      nameInput: '',
      numberInput: '',
      nameInputValid: true,
      numberInputValid: true, // Input Number validity flag
      showCreate: true, // Flag to show/hide Create Event section
      showLimit: false, // Flag to show/hide Limit Event section
      // lastnameInput: '',
      // emailInput: '',
      calendarOptions: {

        //1 day only
        selectAllow: function (selectionInfo) {

          let startDate = selectionInfo.start;
          let endDate = selectionInfo.end;
          endDate.setSeconds(endDate.getSeconds() - 1);  // allow full day selection
          if (startDate.getDate() === endDate.getDate()) {
            return true;
          } else {
            return false;
          }
        },
        plugins: [
          bootstrap5Plugin,
          listPlugin,
          dayGridPlugin,
          timeGridPlugin,
          interactionPlugin // needed for dateClick
        ],
        headerToolbar: {
          left: 'prev,next today',
          center: 'title', 
          right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        // dayCellContent(arg) {
        //   const date = arg.date.getDate(); // Extract the date from the arg object
        //   const customContent = "<div class='custom-content'>Available slot: 10</div>"; // Custom text
        //   return {
        //     html: `<div class='date-cell'><div class='cell-content'>${customContent}</div><div class='date'>${date}</div></div>`
        //   };
        // },
        themeSystem: 'bootstrap5',
        initialView: 'dayGridMonth',
        editable: true,
        selectable: true,
        selectMirror: true,
        dayMaxEvents: true,
        weekends: true,
        select: this.handleDateSelect,
        eventClick: this.deleteItem,
        eventsSet: this.handleEvents,
        eventDrop: this.handleEventDrop,
        eventResize: this.handleEventResize,
        eventDisplay: 'block',
        slotMinTime: '05:00',
        slotMaxTime: '17:00',
        slotDuration: '01:00',
        allDaySlot: false,
       
        /* you can update a remote database when these fire:
        eventAdd:
        eventChange:
        eventRemove:
        */
      },
      
      allevents: []
    }
  },

  mounted() {
    this.fetchEvents();
    this.setSelectedScheduleModal = new bootstrap.Modal(document.getElementById('setSelectedSchedule'), {
      // Optional: specify options here
    });
    this.deletemodal = new bootstrap.Modal(document.getElementById('deleteModal'), {
      // Optional: specify options here
    });

    this.savingConfirmation = new bootstrap.Modal(document.getElementById('savingConfirmation'), {
      // Optional: specify options here
    });

    this.adminEmail = localStorage.getItem('email');
  },

  methods: {

    closeModal() {
      // this.setSelectedScheduleModal.show();
      this.savingConfirmation.hide();
    },

    approval_client() {
      this.$router.push({ name: 'approval' });
    },

    logout() {

      console.log(localStorage.getItem('token'));
      axios.post('http://localhost:8000/api/auth/logout', null, {
        headers: {
          Authorization: `Bearer ${localStorage.getItem('token')}`
        }
      })
        .then(response => {
          localStorage.removeItem('token');
          this.$router.push({ name: 'login' });
          console.log(response.data);
        })
        .catch(error => {
          localStorage.removeItem('token');
          this.$router.push({ name: 'login' });
          console.error('Logout error:', error);
          console.log('An error occurred while logging out. Please try again.');
        });
    },

    fetchEvents() {
      // Fetch events from local storage
      // let eventsFromLocalStorage = JSON.parse(localStorage.getItem('events')) || [];
      // Fetch events from the API
      axios.get('http://localhost:8000/api/events')
        .then(response => {

          let eventsFromAPI = response.data.filter(event => event.user === 'admin' || event.user === 'clientApproved').map(event => {

            let color;
            switch (event.user) {
              case 'clientApproval':
                color = '#007FFF';
                break;
              case 'clientApproved':
                color = '#FF9E00';
                break;
              case 'admin':
                color = '#FF2D00';
                break;
              default:
                // Handle default case if needed
                break;
            }

            return {
              id: event.id,
              title: event.title,
              email: event.email,
              start: event.start,
              end: event.end,
              allDay: event.allDay,
              user: event.user,
              color: color // Assign color based on user type
            };
          });

          this.calendarOptions.events = eventsFromAPI; // Directly assign events to calendarOptions
          console.log(this.calendarOptions.events);

        })
        .catch(error => {
          console.error('Error fetching events:', error);
        });
    },

    //ADD EVENT
    handleDateSelect(selectInfo) {

      this.setSelectedScheduleModal.show();
      this.selectedInfo = selectInfo; // Store selectInfo in selectedInfo
    },

    showCreateSection() {
      if (this.showCreate) {
        // Toggle to show the "Limit" section
        this.showCreate = false;
        this.showLimit = true;
        this.modalTitle = 'Limit Client';
      } else {
        // Toggle to show the "Create" section
        this.showCreate = true;
        this.showLimit = false;
        this.modalTitle = 'Create Schedule';
      }
    },

    saveChanges(selectedInfo) {
      // Reset validity flags

      if (this.showCreate) {
        this.nameInputValid = this.nameInput.trim() !== '';

        if (!this.nameInputValid || !this.nameRegex.test(this.nameInput)) {
          return;
        }
      } else if (this.showLimit) {
        // Check numberInput if showLimit is true
        this.numberInputValid = this.numberInput.trim() !== '';

        if (!this.numberInputValid || !this.numberRegex.test(this.numberInput)) {
          return;
        }
      }
      console.log("Some Error here");

      this.setSelectedScheduleModal.hide();
      this.savingConfirmation.show();
      this.confirmSavingInfo = selectedInfo;
    },

    savingConfirmationEvent(confirmSavingInfo) {

      if (this.showLimit) {
        this.nameInput = this.numberInput;
      }
      this.buttonText = 'Processing';
      this.buttonDisabled = true;

      let calendarApi = confirmSavingInfo.view.calendar
      calendarApi.unselect() // clear date selection

      axios.post('http://localhost:8000/api/events', {

        title: this.nameInput,
        email: this.adminEmail,
        start: formatDatetime(confirmSavingInfo.startStr),
        end: formatDatetime(confirmSavingInfo.endStr),
        user: 'admin',
        allDay: confirmSavingInfo.allDay
      })
        .then(response => {
          // Hide the modal
          this.savingConfirmation.hide();
          this.buttonText = 'Save changes';
          this.buttonDisabled = false;
          // Handle success
          console.log('Event added:', response.data);
          console.log('Event added ID:', response.data.id);
          this.allevents.push(response.data);
          this.fetchEvents();

          // this.newEventTitle = '';
          this.nameInput = '';
          this.lastnameInput = '';
          this.emailInput = '';

        })
        .catch(error => {
          // Handle error
          console.error('Error adding event:', error.response.data);
        });

    },

    deleteItem(clickInfo) {
      console.log("Deleted? : " + clickInfo.event.id);
      this.deletemodal.show();
      this.clickedInfo = clickInfo;
    },

    deleteItemModal(clickedInfo) {
      axios.delete(`http://localhost:8000/api/events/${clickedInfo.event.id}`)
        .then(response => {
          this.deletemodal.hide();
          // this.fetchEvents(); // Refresh events after deleting
          console.log("Succesfully Deleted: " + response.data + clickedInfo.event.id);
          this.fetchEvents();
        })
        .catch(error => {
          console.error('Error deleting event:', error);
        });

    },

    handleEventDrop(info) {
      const eventId = info.event.id;
      const allDay = info.event.allDay;
      const start_formatdate = formatDatetime(info.event.startStr);
      let end_formatdate = formatDatetime(info.event.endStr);

      // If end date is empty, set it to start date
      const moment = require('moment');
      // If end date is empty, set it to start date
      if (end_formatdate === "") {
        end_formatdate = moment(start_formatdate).add(1, 'hours').format('YYYY-MM-DD HH:mm:ss');
      } else {
        end_formatdate = moment(start_formatdate).add(24, 'hours').format('YYYY-MM-DD HH:mm:ss');
      }

      console.log("handleEventDrop : ", eventId, "start_formatdate: " + start_formatdate, "End: " + end_formatdate, "AllDay: ", allDay);

      axios.put(`http://localhost:8000/api/events/${eventId}`, {
        start: start_formatdate,
        end: end_formatdate,
        allDay: allDay
      })
        .then(response => {
          console.log('Event updated:', response.data);

        })
        .catch(error => {
          console.error('Error updating event:', error.response.data);
        });
    },

    // Function to handle resize event
    handleEventResize(info) {
      const eventId = info.event.id;
      const start = formatDatetime(info.event.startStr);
      const end = formatDatetime(info.event.endStr);

      console.log("handleEventResize : ", eventId, "start ", start, "  end ", end);

      axios.put(`http://localhost:8000/api/events/${eventId}`, {
        start: start,
        end: end,
        allDay: false
      })
        .then(response => {
          console.log('Event updated Resize:', response.data);
        })
        .catch(error => {
          console.error('Error updating event:', error.response.data);
        });
    },

    handleEvents(events) {
      this.allevents = events;
    }
  },


}
</script>


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
  <router-link to="/approval" class="nav-link">
    <i class="fas fa-hourglass-half"></i> Patient Approval
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

    <!-- Admin Main Content -->
    <div class='admin-app-main' style="margin-left: 250px;">
  <FullCalendar ref="fullCalendar" class='admin-app-calendar' :options='calendarOptions'>
    <template v-slot:eventContent='arg'>
      <b>{{ arg.start }}</b>
      <i>{{ arg.event.title }}</i>
    </template>
  </FullCalendar>
</div>

<!-- Modals -->

<!-- Modal: Set Selected Schedule -->
<div class="modal fade" id="setSelectedSchedule" tabindex="-1" aria-labelledby="setSelectedScheduleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="setSelectedScheduleModalLabel">Set Selected Schedule</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <!-- Modal Body Content -->
      <div class="modal-body">
        <div class="mb-3">
          <label for="nameInput" class="form-label">Schedule for holiday</label>
          <input type="text" class="form-control" id="nameInput" v-model="nameInput" placeholder="Input Name">
          <small v-if="!nameInput && !nameInputValid" class="text-danger">Please fill the input field</small>
          <small v-else-if="nameInput && !nameRegex.test(nameInput)" class="text-danger">Please fill valid Schedule</small>
        </div>
        <div class="mb-3">
          <label for="numberInput" class="form-label">Number of clients to accept</label>
          <input type="text" class="form-control" id="numberInput" v-model="numberInput" placeholder="Enter a number">
          <small v-if="!numberInput && !numberInputValid" class="form-text text-danger">Please fill the input field</small>
          <small v-else-if="numberInput && !numberRegex.test(numberInput)" class="form-text text-danger">Please enter a valid number</small>
        </div>
        <div class="row mb-3">
          <div class="col-md-6">
            <label for="startTime" class="form-label">Start Time</label>
            <select id="startTime" name="startTime" v-model="selectedStartTime" class="form-select">
              <!-- Options here -->
            </select>
          </div>
          <div class="col-md-6">
            <label for="endTime" class="form-label">End Time</label>
            <select id="endTime" name="endTime" v-model="selectedEndTime" class="form-select">
              <!-- Options here -->
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success me-auto" @click="showCreateSection">{{ showCreate ? 'Limit Client' : 'Busy day' }}</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" @click="saveChanges(selectedInfo)">Create Schedule</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Saving Confirmation -->
<div class="modal fade" id="savingConfirmation" tabindex="-1" aria-labelledby="savingConfirmationLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="savingConfirmationLabel">Saving Confirmation</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <!-- Modal Body Content -->
      <div class="modal-body">
        <h4>You can't edit your schedule once it's saved.</h4>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" @click="closeModal()">Close</button>
        <button type="button" class="btn btn-primary" @click="savingConfirmationEvent(selectedInfo)" :disabled="buttonDisabled">{{ buttonText }}</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Delete -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white ">
        <h5 class="modal-title">Delete</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <h4>Are you sure you want to delete?</h4>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" @click="deleteItemModal(clickedInfo)">Delete</button>
      </div>
    </div>
  </div>
</div>
</div>
</template>

<style src="@/styles/AdminViewStyles.css">



</style>

