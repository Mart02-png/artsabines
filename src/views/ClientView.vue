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
      selectedDate: '',
      selectedTime: '',
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
      numberRegex: /^(09|63)\d{9}$/,
      showEmailPhoneError: false,
      nameInputValid: true,
        lastnameInputValid: true,
        emailInputValid: true,
        validEmail: true,
        validNumber: true,

      calendarOptions: {
        customButtons: {
          manuallyAddTime: {
            text: 'Manually add Time',
            click: this.manuallyAddTime
          }
        },
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
          left: 'prev,next today manuallyAddTime',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
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
        slotMaxTime: '17:00',
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

    saveDateTimeRange() {
      
      // console.log("Selected Date:", this.selectedDate);
      // console.log("Selected Time:", document.getElementById("time").value);
    },

    manuallyAddTime() {
      this.modalTitle = 'Manually create Schedule';
      this.showDateTimeInputs = true;
        // Implement the logic for adding time manually here
        
      this.modal.show();
    },

    fetchEvents() {
      axios.get('http://localhost:8000/api/events')
        .then(response => {
          let eventsFromAPI = response.data.filter(event => event.user === 'clientApproval').map(event => {
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
        })
        .catch(error => {
          console.error('Error fetching events:', error);
        });
    },

    handleDateSelect(selectInfo) {
      this.modalTitle = 'Create Schedule';
      this.showDateTimeInputs = false;
      if (this.showDateTimeInputs) {
          console.log("Get date from manual add time");
        } else {
          console.log("Get date from selected date");
        }
    const moment = require('moment');

    const selectedTime = moment(selectInfo.startStr).format('HH:mm');
    const currentTime = moment().format('HH:mm');
    // console.log("selectedTime", selectedTime, "currentTime", currentTime);

    if (selectInfo.view.type === 'dayGridMonth' &&
        ((currentTime >= '00:00' && currentTime < '05:00') ||
        (currentTime >= '12:00' && currentTime < '13:00') ||
        (currentTime >= '17:00' && currentTime < '24:00'))) {
        updateErrorMessage("No Appointment on this hours");
        this.errorModal.show();
    } else if ((selectInfo.view.type === 'timeGridWeek' || selectInfo.view.type === 'timeGridDay') &&
        ((selectedTime >= '00:00' && selectedTime < '05:00') ||
        (selectedTime >= '12:00' && selectedTime < '13:00') ||
        (selectedTime >= '17:00' && selectedTime < '24:00'))) {
        updateErrorMessage("No Appointment on this hours");
        this.errorModal.show();
    } else {
        this.checkEventOverlap(selectInfo);
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

      if (this.showDateTimeInputs) {
        let selectedTimePlusOneHour = moment(this.selectedTime, 'HH:mm').add(1, 'hour').format('HH:mm');
        console.log("Get date from manual add time", this.showDateTimeInputs , confirmSavingInfo);
        lastStart = this.selectedDate + ' ' + this.selectedTime;
        lastEnd = this.selectedDate + ' ' + selectedTimePlusOneHour;
        // console.log("lastStart", lastStart, "lastEnd", lastEnd, "selectedTimePlusOneHour", selectedTimePlusOneHour);
      } else {
        let calendarApi = confirmSavingInfo.view.calendar;
        calendarApi.unselect() // clear date selection

        let currentTimeNow = moment().format('HH:00:00');
        let startNow = currentTimeNow;
        let endNow = moment(currentTimeNow, 'HH:00:00').add(1, 'hour').format('HH:00:00');

        // Format start and end dates for dayGridMonth
        let start = formatDatetime(confirmSavingInfo.startStr);
        let end = formatDatetime(confirmSavingInfo.endStr);

        console.log("Get date from selected date", this.showDateTimeInputs , confirmSavingInfo);

        if (confirmSavingInfo.view.type === 'dayGridMonth'){
          lastStart = start + ' ' + startNow;
          lastEnd = end + ' ' + endNow;
        }else if(confirmSavingInfo.view.type === 'timeGridWeek' || confirmSavingInfo.view.type === 'timeGridDay'){
          lastStart = start;
          lastEnd = end;
        }
        
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
            <router-link to="/landing" class="nav-link">
              <h6 style="font-weight: bold; font-size: 1.3em; ">Landing Page</h6>
              <i class="fas fa-info-circle"></i> About Us
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
      <div style="text-align: center; margin-top: 20px;">
  <router-link to="/admin">
    <i class="fas fa-user-tie" style="font-size: 24px; color: #333;"></i>
  </router-link>
</div>
      <!--for admin-->
    </div>
  





    <div class='demo-app-main'>
      <FullCalendar class='demo-app-calendar' :options='calendarOptions'>
        <template v-slot:eventContent='arg'>
          <b>{{ arg.start }}</b>
          <i>{{ arg.event.title }}</i>
        </template>
      </FullCalendar>

      <div class="footer-content">
  <p>&copy; 2024 Gentle Care Clinic. All rights reserved.</p>
  <p></p>
</div>


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
                <label v-if="showDateTimeInputs" for="date">Date:</label>
                <input v-if="showDateTimeInputs" type="date" id="date" v-model="selectedDate">
            
                <label v-if="showDateTimeInputs" for="time">Time:</label>
                  <select v-if="showDateTimeInputs" id="time" name="time" v-model="selectedTime">
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
              <!-- Rest of your modal content -->
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
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" @click="closeModal()">Cancel</button>
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
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
              </div>
            </div>
          </div>
        </div>

    </div>
  </div>


 
</template>




<style src="@/styles/ClientViewStyles.css">



</style>

