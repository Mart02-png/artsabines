<!-- ClientView.vue -->

<script>

import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'
import listPlugin from '@fullcalendar/list'

import axios from 'axios';
import bootstrap5Plugin from '@fullcalendar/bootstrap5'
import * as bootstrap from 'bootstrap';

function formatDatetime(datetimeStr) {
  return datetimeStr.replace('T', ' ').replace(/\+.*$/, '');
}

function updateErrorMessage(message) {
  const errorMessageElement = document.getElementById('errorMessage');
  errorMessageElement.textContent = message;
}

export default {
  components: {
    FullCalendar
  },

  data: function () {
    return {
      showDateTimeInputs: false,
      selectedTime: '', // Time selected from the dropdown
      buttonText: 'Save changes',
      modalTitle: 'Create Schedule',
      buttonDisabled: false,
      modal: null,
      errorModal: null,
      savingConfirmation: null,
      nameInput: '',
      lastnameInput: '',
      emailInput: '',
      nameRegex: /^[a-zA-Z\s]*$/,
      // emailRegex: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
      emailRegex: /^[a-zA-Z0-9._]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
      numberRegex: /^09\d{9}|^63\d{10}$/,
      showEmailPhoneError: false,
      nameInputValid: true,
        lastnameInputValid: true,
        emailInputValid: true,
        validEmail: true,
        validNumber: true,

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
          right: 'dayGridMonth,timeGridWeek,timeGridDay,listDay'
        },
        themeSystem: 'bootstrap5',
        initialView: 'dayGridMonth',
        editable: false,
        selectable: true,
        selectMirror: true,
        dayMaxEvents: true,
        weekends: true,
        select: this.handleDateSelect,
        eventsSet: this.handleEvents,
        eventDisplay: 'block',
        slotMinTime: '05:00',
        slotMaxTime: '18:00',
        slotDuration: '01:00',
        allDaySlot: false,
      },
      allevents: [],
    }
  },

  mounted() {
    this.fetchEvents();
    this.modal = new bootstrap.Modal(document.getElementById('exampleModal'), {
      // Optional: specify options here
    });
    this.errorModal = new bootstrap.Modal(document.getElementById('errorModal'), {
      // Optional: specify options here
    });
    
    this.savingConfirmation = new bootstrap.Modal(document.getElementById('savingConfirmation'), {
      // Optional: specify options here
    });
    

  },

  methods: {

    fetchEvents() {
      axios.get('http://localhost:8000/api/events')
        .then(response => {
          let eventsFromAPI = response.data.filter(event => event.user === 'clientApproval' || event.user === 'admin').map(event => {
            let color;
            switch (event.user) {
              case 'clientApproval':
                color = '#007FFF';
                break;
              case 'admin':
                color = '#FF2D00';
                break;
              default:
                color = '#007FFF';
                break;
            }

            return {
              id: event.id,
              title: event.title,
              start: event.start,
              end: event.end,
              allDay: event.allDay,
              user: event.user,
              color: color // Assign color based on user type
            };
          });
          this.calendarOptions.events = eventsFromAPI;
          this.allevents.push(eventsFromAPI);
          console.log("eventsFromAPI", eventsFromAPI, "allevents", this.allevents);

        })
        .catch(error => {
          console.error('Error fetching events:', error);
        });
    },

    handleDateSelect(selectInfo) {
      console.log("selectInfo", selectInfo, "allevents", this.allevents);
      this.modalTitle = 'Create Schedule';

      const moment = require('moment');

      const selectedTime = moment(selectInfo.startStr).format('HH:mm');
      const currentTime = moment().format('HH:mm');

      if (selectInfo.view.type === 'dayGridMonth' &&
          (currentTime >= '12:00' && currentTime < '13:00')) {
          updateErrorMessage("Lunch break");
          this.errorModal.show();
      } else if ((selectInfo.view.type === 'timeGridWeek' || selectInfo.view.type === 'timeGridDay') &&
          (selectedTime >= '12:00' && selectedTime < '13:00')) {
          updateErrorMessage("Lunch break");
          this.errorModal.show();
      } else {
          this.checkEventOverlap(selectInfo);
      }

      if (selectInfo.view.type === 'dayGridMonth'){
        this.showDateTimeInputs = true;
      } else if(selectInfo.view.type === 'timeGridWeek' || selectInfo.view.type === 'timeGridDay'){
        this.showDateTimeInputs = false;
      }
    },

    checkEventOverlap(selectInfo) {
      const moment = require('moment');
      const currentTime = moment().format('HH:mm:ss');
      let startSelectedDate = selectInfo.startStr + " " + currentTime;

      axios.get('http://localhost:8000/api/events')
        .then(response => {
          let canAddEvent = true;
          response.data.forEach(event => {
            const moment = require('moment');
            let startSelectedDateNoTime = moment(selectInfo.startStr).format('YYYY-MM-DD');
            let EndSelectedDateNoTime = moment(event.start).format('YYYY-MM-DD');
            if (event.user === 'admin' && event.allDay === 1 && startSelectedDateNoTime === EndSelectedDateNoTime || event.user === 'admin' && startSelectedDate >= event.start && startSelectedDate <= event.end) {
              canAddEvent = false;
              updateErrorMessage("The schedule is busy on this day. Please select another date");
              this.errorModal.show();
            }
          });

          if (canAddEvent) {
            this.modal.show();
            this.selectedInfo = selectInfo;
          }
        })
        .catch(error => {
          console.error('Error fetching events:', error);
        });
    },

    closeModal(){
      this.modal.show();
      this.savingConfirmation.hide();
    },

    savingConfirmationEvent(confirmSavingInfo) {
      this.buttonText = 'Processing';
      this.buttonDisabled = true;

      this.newEventTitle = this.nameInput + ' ' + this.lastnameInput;
      // Format start and end dates for timeGridWeek and timeGridDay
      const moment = require('moment');
      
      let lastStart = '';
      let lastEnd = '';

      // if (this.showDateTimeInputs) {
      //   let selectedTimePlusOneHour = moment(this.selectedTime, 'HH:mm').add(1, 'hour').format('HH:mm');
      //   console.log("Get date from manual add time", this.showDateTimeInputs , confirmSavingInfo);
      //   lastStart = this.selectedDate + ' ' + this.selectedTime;
      //   lastEnd = this.selectedDate + ' ' + selectedTimePlusOneHour;
      //   // console.log("lastStart", lastStart, "lastEnd", lastEnd, "selectedTimePlusOneHour", selectedTimePlusOneHour);
      // } else {
        let calendarApi = confirmSavingInfo.view.calendar;
        calendarApi.unselect() // clear date selection

        // let currentTimeNow = moment().format('HH:00:00');
        let startNow = this.selectedTime;
        let endNow = moment(startNow, 'HH:00:00').add(1, 'hour').format('HH:00:00');

        // Format start and end dates for dayGridMonth
        let start = formatDatetime(confirmSavingInfo.startStr);
        let end = formatDatetime(confirmSavingInfo.endStr);

        if (confirmSavingInfo.view.type === 'dayGridMonth'){
          lastStart = start + ' ' + startNow;
          lastEnd = start + ' ' + endNow;
        }else if(confirmSavingInfo.view.type === 'timeGridWeek' || confirmSavingInfo.view.type === 'timeGridDay'){
          lastStart = start;
          lastEnd = end;
        }
      console.log("lastStart", lastStart, "lastEnd", lastEnd);

      
        axios.post('http://localhost:8000/api/events', {
          title: this.newEventTitle,
          email: this.emailInput,
          start: lastStart,
          end: lastEnd,
          user: 'clientApproval',
          allDay: false
        })
          .then(response => {
            console.log(response.data);
            this.savingConfirmation.hide();
            this.buttonText = 'Save changes';
            this.buttonDisabled = false;
            this.allevents.push(response.data);

            // calendarApi.addEvent(response.data);
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

    saveChanges(selectedInfo) {

         // Reset validity flags
    this.nameInputValid = this.nameInput.trim() !== '';
    this.lastnameInputValid = this.lastnameInput.trim() !== '';
    this.showEmailPhoneError = this.emailInput.trim() !== '';
    this.emailInputValid = this.emailInput.trim() !== '';
    this.validEmail = this.emailRegex.test(this.emailInput);
    this.validNumber = this.numberRegex.test(this.emailInput);

    // Check if all inputs are filled

  // Check if all inputs are filled
  if (!this.nameInputValid || !this.lastnameInputValid || !this.emailInputValid) {
    return;
  }

  // Validation for Name Input
  if (!this.nameRegex.test(this.nameInput)) {
    this.nameInputValid = false;
    return;
  }

  // Validation for Lastname Input
  if (!this.nameRegex.test(this.lastnameInput)) {
    this.lastnameInputValid = false;
    return;
  }

  // Validation for Email Input
  if (!this.emailRegex.test(this.emailInput) && !this.numberRegex.test(this.emailInput)) {
    this.emailInputValid = false;
    return;
  }

      this.modal.hide();
      this.savingConfirmation.show();
      this.confirmSavingInfo = selectedInfo;
    },

  }
}
</script>

<template>
  <div class='demo-app'>
    <div class='demo-app-sidebar' style="display: flex; flex-direction: column; justify-content: space-between;">
      <div>
        <div class="text-center">
          <img src="../assets/icon.png" alt="Ears Nose and Throat" style="width: 160px; height: auto; "
            class="img-fluid">
        </div>
        <div style="margin: 50px 5px 10px 5px ;">
          <h2>Instructions</h2>
          <ul style="text-align: left;">
            <li>Select dates and you will be prompted to create a new event</li>
            <li>Drag, drop, and resize events</li>
            <li>Click an event to delete it</li>
          </ul>
        </div>
      </div>

      <!-- Admin Login -->
      <div class="text-center" style="margin: 10px 5px 10px 5px ;">
        <router-link :to="{ name: 'about' }">about</router-link>
        <router-view />
      </div>

      <!-- Admin Login -->
      <div class="text-center" style="margin: 10px 5px 10px 5px ;">
        <router-link :to="{ name: 'login' }">Admin Login</router-link>
        <router-view />
      </div>
    </div>


    <div class='demo-app-main'>
      <FullCalendar class='demo-app-calendar' :options='calendarOptions'>
        <template v-slot:eventContent='arg'>
          <b>{{ arg.start }}</b>
          <i>{{ arg.event.title }}</i>
        </template>
      </FullCalendar>

      <!-- Modal -->
      <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header bg-primary text-white">
              <h5 class="modal-title" id="exampleModalLabel">{{ modalTitle }} </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <!-- Modal Body Content -->
            <div class="modal-body">

             
              <div class="mb-3">
                <input type="text" class="form-control" id="nameInput" v-model="nameInput" placeholder="Name">
                <small v-if="!nameInput && !nameInputValid" class="text-danger">Please fill Name</small>
                <small v-else-if="nameInput && !nameRegex.test(nameInput) && !numberRegex.test(nameInput)" class="text-danger">Please fill valid Name</small>
              </div>

              <div class="mb-3">
                  <input type="text" class="form-control" id="lastnameInput" v-model="lastnameInput" placeholder="Lastname">
                  <small v-if="!lastnameInput && !lastnameInputValid" class="text-danger">Please fill Lastname</small>
                  <small v-else-if="lastnameInput && !nameRegex.test(lastnameInput) && !numberRegex.test(lastnameInput)" class="text-danger">Please fill valid valid Lastname</small>
              </div>
              <div class="mb-3">
                <input type="email" class="form-control" id="emailInput" v-model="emailInput" placeholder="Email" autocomplete="current-emailInput" required>
                <small v-if="!emailInput && !emailInputValid" class="text-danger">Please fill contact information</small>
                <small v-else-if="emailInput && !emailRegex.test(emailInput) && !numberRegex.test(emailInput)" class="text-danger">Please fill valid Email or valid phone number</small>
              </div>
              <div class="mb-3">
                <label v-if="showDateTimeInputs" for="time">Time:</label>
                  <select v-if="showDateTimeInputs"  id="time" name="time" v-model="selectedTime">
                    <option value="05:00:00">05:00 AM</option>
                    <option value="06:00:00">06:00 AM</option>
                    <option value="07:00:00">07:00 AM</option>
                    <option value="08:00:00">08:00 AM</option>
                    <option value="09:00:00">09:00 AM</option>
                    <option value="10:00:00">10:00 AM</option>
                    <option value="11:00:00">11:00 AM</option>
                    <option value="01:00:00">01:00 PM</option>
                    <option value="02:00:00">02:00 PM</option>
                    <option value="03:00:00">03:00 PM</option>
                    <option value="04:00:00">04:00 PM</option>
                    <option value="05:00:00">05:00 PM</option>
                  </select>
              </div>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="button" class="btn btn-primary" @click="saveChanges(selectedInfo)">Save changes</button>
            </div>
          </div>
        </div>
      </div>


            <!-- Saving Confirmation -->
      <div class="modal fade" id="savingConfirmation" tabindex="-1" aria-labelledby="savingConfirmation" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header bg-primary text-white">
              <h5 class="modal-title" id="exampleModalLabel">Are you sure you want to save?</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <!-- Modal Body Content -->
            <div class="modal-body">
              <h4>
                  You cant edit or delete your schedule once it saved.
                </h4>
            </div>
              
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" @click="closeModal()">Close</button>
              <button type="button" class="btn btn-primary" @click="savingConfirmationEvent(selectedInfo)" :disabled="buttonDisabled"> {{ buttonText }} </button>
            </div>
          </div>
        </div>
      </div>

        <!-- Error -->
        <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModal" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header bg-danger text-white">
                <h5 class="modal-title bi-exclamation-triangle-fill"> Error</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                  aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <h4>
                  <p><i class="bi"></i> <span id="errorMessage"></span></p>
                </h4>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>

    </div>
  </div>
</template>



<style lang='css'>
html,
body {
  height: 100vh;
  margin: 0;
  padding: 0;

  /* overflow: hidden; Disable scrolling of the entire page */
}

.time-slot-occupied {
  background-color: red;
  color: white;
  font-weight: bold;
}

@media (max-width: 650px) {
  ul {
    margin: 0;
    /* padding: 0 0 0 1.5em; */
    font-size: 10px;
  }

  li {
    /* margin: 1.5em 0; */
    padding: 0;
    font-size: 10px;
  }
}

/* .modal-backdrop {
    display: none;
    z-index: 1040 !important;
}

.modal-content {
    margin: 2px auto;
    z-index: 1100 !important;
} */

h2 {
  margin: 0;
  font-size: 16px;
}

ul {
  margin: 0;
  /* padding: 0 0 0 1.5em; */
}

li {
  /* margin: 1.5em 0; */
  padding: 0;
}

b {
  /* used for event dates/times */
  margin-right: 3px;
  color: black;
}

i {
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
}

.demo-app {
  display: flex;
  font-family: Arial, Helvetica Neue, Helvetica, sans-serif;
  font-size: 14px;
  max-height: 100vh;
  /* Set sidebar height to match viewport height */
}

.demo-app-sidebar {
  padding: 10px;
  width: 300px;
  line-height: 1.5;
  /* background: #eaf9ff; */
  background-color: #e6e7e9;
  border-right: 1px solid #d3e2e8;
  /* overflow-y: auto; */
  max-height: 100vh;
  /* Set sidebar height to match viewport height */
}

.demo-app-main {
  flex-grow: 1;
  padding: 3em;
}

/* Adjust the height of FullCalendar to fit within the main content area */
.demo-app-calendar {
  height: calc(100vh - 6em);
  /* Adjust as needed, considering header/footer heights */
}

.fc {
  /* the calendar root */
  /* max-width: 1100px; */
  margin: 0 auto;
  max-height: 100vh;
  /* Set sidebar height to match viewport height */
}

.fc-col-header-cell-cushion {
  text-decoration: none;
  color: black;
}

.fc-scrollgrid .fc-daygrid-day-number {
  text-decoration: none;
  color: black;

}

.fc-event {
  color: white;
}

.fc-list-event-title {
  color: black;
}

.fc-list-event-time {
  color: black;
}

.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
}


/* Make the text bold in the week view of FullCalendar */
.fc-timegrid-event .fc-content {
  /* For event titles */
  font-weight: bold;
}

.fc-timegrid-axis,
/* For time labels on the left */
.fc-day-header,
/* For day headers */
.fc-timegrid-slots td,
/* For time slots */
.fc-timegrid-slots .fc-timegrid-slot-lane {
  /* For the content within time slots */
  font-weight: bold;
}


/* Make the dates bold in FullCalendar */
.fc-daygrid-day-number {
  /* For day numbers in month view */
  font-weight: bold;
}

.fc-col-header {
  /* For day headers (e.g., Sun, Mon, Tue) */
  font-weight: bold;
  background-color: white;
  font-size: 20px;
}

.fc table {
  font-size: 14px;
}


/* Make the text bold in FullCalendar */
.fc-daygrid-event,
/* Event titles */
.fc-list-item-title

/* List view event titles */
  {
  font-weight: bold;
  overflow: hidden;
}

.fc-toolbar-title

/* Calendar header title */
  {
  font-weight: bold;
  color: white;
}

.fc-view-harness {
  background-color: rgba(230, 231, 233, 0.783);
  /* Adjust the alpha value (0.5) to make it more or less transparent */
}

:root {
  --fc-list-event-hover-bg-color: #ADD8E6;
  /* Light blue color */
  --fc-today-bg-color: rgba(77, 76, 75, 0.53);
}

.fc-day:hover {
  background-color: #ADD8E6;
  /* Light blue color on hover */
  cursor: pointer;
  /* Change cursor to pointer on hover */
}

.fc-event-time,
.fc-event-title {
  display: inline;
}

.fc-timegrid-slot {
  line-height: 42.5px;
}
</style>
