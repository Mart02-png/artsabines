<script>

import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'
import listPlugin from '@fullcalendar/list'

import axios from 'axios';
import bootstrap5Plugin from '@fullcalendar/bootstrap5'
import * as bootstrap from 'bootstrap';
// import { options } from '@fullcalendar/core/preact'
// import { format } from 'core-js/core/date'


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
      timeOptions: [
        { value: '05:00:00', label: '05:00 AM' },
        { value: '06:00:00', label: '06:00 AM' },
        { value: '07:00:00', label: '07:00 AM' },
        { value: '08:00:00', label: '08:00 AM' },
        { value: '09:00:00', label: '09:00 AM' },
        { value: '10:00:00', label: '10:00 AM' },
        { value: '11:00:00', label: '11:00 AM' },
        { value: '13:00:00', label: '01:00 PM' },
        { value: '14:00:00', label: '02:00 PM' },
        { value: '15:00:00', label: '03:00 PM' },
        { value: '16:00:00', label: '04:00 PM' },
        { value: '17:00:00', label: '05:00 PM' }
      ],
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
          let eventsFromAPI = response.data.filter(event => event.user === 'clientApproval' || event.user === 'admin' || event.user === 'adminLimit').map(event => {
            let color;
            switch (event.user) {
              case 'clientApproval':
                color = '#007FFF';
                break;
              case 'admin':
                color = '#FF2D00';
                break;
              case 'adminLimit':
                color = '#0047AB';
                event.title = `Available slot: ${event.title}`;
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

        })
        .catch(error => {
          console.error('Error fetching events:', error);
        });
    },

  handleDateSelect(selectInfo) {
    this.selectedTime = '05:00:00'
    this.timeOptions= [
        { value: '05:00:00', label: '05:00 AM' },
        { value: '06:00:00', label: '06:00 AM' },
        { value: '07:00:00', label: '07:00 AM' },
        { value: '08:00:00', label: '08:00 AM' },
        { value: '09:00:00', label: '09:00 AM' },
        { value: '10:00:00', label: '10:00 AM' },
        { value: '11:00:00', label: '11:00 AM' },
        { value: '13:00:00', label: '01:00 PM' },
        { value: '14:00:00', label: '02:00 PM' },
        { value: '15:00:00', label: '03:00 PM' },
        { value: '16:00:00', label: '04:00 PM' },
        { value: '17:00:00', label: '05:00 PM' }
      ];

    axios.get('http://localhost:8000/api/events')
    .then(response => {
      response.data.forEach(event => {
        const moment = require('moment');
        const startEventTime = moment(event.start).format('HH:mm:ss');
        const endEventTime = moment(event.end).format('HH:mm:ss');

        const startEventDate = moment(event.start).format('YYYY-MM-DD');
        const endEventDate = moment(event.end).format('YYYY-MM-DD');

        if (startEventDate !== selectInfo.startStr && endEventDate !== selectInfo.endStr) {
          return this.timeOptions;
        }

        
        if (event.user === 'admin' || event.user === 'clientApproval') {
          this.timeOptions = this.timeOptions.filter(timeOption => {
            return timeOption.value < startEventTime || timeOption.value >= endEventTime;
          });
        }

        if (this.timeOptions.length > 0) {
          this.selectedTime = this.timeOptions[0].value;
          console.log("arts");
        }

      });
    })
    .catch(error => {
      console.error('Error fetching events:', error);
    });



      const moment = require('moment');
  
      //   //Recheck if dat already pass
      let selectedDate = moment(selectInfo.startStr).format('YYYY-MM-DD');
      let currentDate = moment().format('YYYY-MM-DD HH:mm');
      let currentTime2 = moment().format('HH:mm');
      let selectedDatefull = selectedDate + ' ' + currentTime2;

      let endNow = moment(selectedDatefull).add(1, 'minute').format('YYYY-MM-DD HH:mm');

      // Check if the selected date is in the past, if currentDate and selectedDatefull are equal or currentDate is after selectedDatefull, then proceed
      if (currentDate > endNow) {
        console.log('Selected date is in the past. Cannot add schedule.');
        updateErrorMessage("Date already passed away, you cannot add schedule on this date.");
        this.errorModal.show();
        return; // Exit function if date is in the past
      }

      this.modalTitle = 'Create Schedule';

      // const moment = require('moment');

      const selectedTime = moment(selectInfo.startStr).format('HH:mm');
      if ((selectInfo.view.type === 'timeGridWeek' || selectInfo.view.type === 'timeGridDay') &&
          (selectedTime >= '12:00' && selectedTime < '13:00')) {
          updateErrorMessage("Lunch break");
          this.errorModal.show();
      } else {
          this.modal.show();
          this.selectedInfo = selectInfo;
          // this.checkEventOverlap(selectInfo);
      }

      if (selectInfo.view.type === 'dayGridMonth'){
        this.showDateTimeInputs = true;
      } else if(selectInfo.view.type === 'timeGridWeek' || selectInfo.view.type === 'timeGridDay'){
        this.showDateTimeInputs = false;
      }
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

        axios.post('http://localhost:8000/api/events', {
          title: this.newEventTitle,
          email: this.emailInput,
          start: lastStart,
          end: lastEnd,
          user: 'clientApproval',
          allDay: false
        })
          .then(response => {
            this.savingConfirmation.hide();
            this.buttonText = 'Save changes';
            this.buttonDisabled = false;
            this.allevents.push(response.data);

            this.fetchEvents();

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

    axios.get('http://localhost:8000/api/events')
        .then(response => {
          let canAddEvent = true;
          response.data.forEach(event => {
            
            const moment = require('moment');
            const startEvent = moment(event.start).format('HH:mm:ss');
            const endEvent = moment(event.end).format('HH:mm:ss');
           
            
            if (event.user === 'adminLimit' && this.selectedTime >= startEvent && this.selectedTime <= endEvent) {
              console.log("Check to limit");
            }
          });

          if(canAddEvent){
            this.modal.hide();
            this.savingConfirmation.show();
            this.confirmSavingInfo = selectedInfo;
          }
          
        })
        .catch(error => {
          console.error('Error fetching events:', error);
        });

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
                  <option :value="time.value" 
                    v-for="time in timeOptions" 
                    :key="time.value">
                    {{ time.label }}
                  </option>
                </select>
              </div>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" @click="closeModal()">Back</button>
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
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Back</button>
              </div>
            </div>
          </div>
        </div>

    </div>
  </div>
</template>
<style src="@/styles/styles.css">
</style>
