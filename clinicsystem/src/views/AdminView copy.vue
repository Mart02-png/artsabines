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
      inputPlaceholder: 'Schedule Name',
      // showInputsNumAplha: false,
      buttonText: 'Save changes',
      modalTitle: 'Create Schedule',
      buttonDisabled: false,
      modal: null,
      deletemodal: null,
      limitModal: null,
      savingConfirmation: null,
      numberModal: null,
      adminEmail: '',


      nameRegex: /^[a-zA-Z\s]*$/,
      numberRegex: /^\d+$/,
      nameInput: '',
      numberInput: '',
      nameInputValid: false,
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
          right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
        },
        // dayCellContent(arg) {
        //   const date = arg.date.getDate(); // Extract the date from the arg object
        //   const customContent = "<div class='custom-content'>Client Left: 10</div>"; // Custom text
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
    this.modal = new bootstrap.Modal(document.getElementById('exampleModal'), {
      // Optional: specify options here
    });
    this.deletemodal = new bootstrap.Modal(document.getElementById('deleteModal'), {
      // Optional: specify options here
    });
    this.limitModal = new bootstrap.Modal(document.getElementById('limitModal'), {
      // Optional: specify options here
    });
    
    this.savingConfirmation = new bootstrap.Modal(document.getElementById('savingConfirmation'), {
      // Optional: specify options here
    });

    this.numberModal = new bootstrap.Modal(document.getElementById('numberModal'), {
      // Optional: specify options here
    });
    this.adminEmail = localStorage.getItem('email');
  },

  methods: {
    
    closeModal(){
      this.modal.show();
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
      let eventsFromLocalStorage = JSON.parse(localStorage.getItem('events')) || [];
      // Fetch events from the API
      axios.get('http://localhost:8000/api/events')
        .then(response => {

            let eventsFromAPI = response.data.filter(event => event.user === 'admin' ||  event.user === 'clientApproved' ).map(event => {
          
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
                start: event.start,
                end: event.end,
                allDay: event.allDay,
                user: event.user,
                color: color // Assign color based on user type
            };
        });

          // Combine events from local storage and API
          let allEvents = eventsFromLocalStorage.concat(eventsFromAPI);

          // Update the calendar events with combined events
          // this.$set(this.calendarOptions, 'events', allEvents);
          this.calendarOptions.events = allEvents; // Directly assign events to calendarOptions

        })
        .catch(error => {
          console.error('Error fetching events:', error);
        });
    },

    //ADD EVENT
    handleDateSelect(selectInfo) {

      // this.modal.show();
      this.limitModal.show();
      this.selectedInfo = selectInfo; // Store selectInfo in selectedInfo
    },

    createEventSelected(selectedInfo){
      this.inputPlaceholder = 'Schedule Name'; 
      this.limitModal.hide();
      this.modal.show();
      // this.limitModal.show();
      // this.showInputsNumAplha = true;
      this.selectedInfo = selectedInfo; // Store selectInfo in selectedInfo
      this.modalTitle = 'Create Schedule';
      
    },

    limitEventSelected(selectedInfo){
      this.limitModal.hide();
      this.numberModal.show();
      this.selectedInfo = selectedInfo; // Store selectInfo in selectedInfo
      this.inputPlaceholder = 'Limit Client Schedule'; 
      this.modalTitle = 'Limit date Schedule';
      console.log(selectedInfo);
    },

    saveChanges(selectedInfo) {

      // Reset validity flags
      this.nameInputValid = this.nameInput.trim() !== '';

      if (!this.nameInputValid) {
        return;
      }
      if (!this.nameRegex.test(this.nameInput)) {
        this.nameInputValid = false;
        return;
      }

      this.modal.hide();
      this.savingConfirmation.show();
      this.confirmSavingInfo = selectedInfo;
    },

    numbersaveChanges(selectedInfo){
        // Reset validity flags
        this.nameInputValid = this.nameInput.trim() !== '';

        if (!this.nameInputValid) {
          return;
        }
        if (!this.numberRegex.test(this.nameInput)) {
          this.nameInputValid = false;
          return;
        }

        this.numberModal.hide();
        this.savingConfirmation.show();
        this.confirmSavingInfo = selectedInfo;
    },

    savingConfirmationEvent(confirmSavingInfo) {
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

    deleteItemModal(clickedInfo){
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
      }else{
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
  <div class='demo-app'>
    <div class='demo-app-sidebar' style="display: flex; flex-direction: column; justify-content: space-between;">
  <div>
    <div class="text-center">
      <img src="../assets/icon.png" alt="Ears Nose and Throat" style="width: 160px; height: auto; " class="img-fluid">
      <h4>{{ adminEmail }}</h4>
    </div>
    <div style="margin: 50px 5px 10px 5px ;">
    </div>
  </div>

  <!-- Admin Logout -->
  <div class="text-center" style="margin: 10px 5px 10px 5px;">
    <button @click="approval_client" class="btn btn-primary">Client Approval</button>
  </div>

  <!-- Admin Logout -->
  <div class="text-center" style="margin: 10px 5px 10px 5px;">
    <button @click="logout" class="btn btn-danger">Admin Logout</button>
  </div>
</div>


    <div class='demo-app-main'>

      <FullCalendar class='demo-app-calendar' :options='calendarOptions'>
        <template v-slot:eventContent='arg'>
          <b>{{ arg.start }}</b>
          <i>{{ arg.event.title }}</i>
        </template>
      </FullCalendar>
    </div>

     <!-- Modal -->
     <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header bg-primary text-white">
              <h5 class="modal-title" id="exampleModalLabel">{{ modalTitle }}</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
              <!-- Modal Body Content -->
              <div class="modal-body">  

                <div class="mb-3">

                  <div class="mb-3">
                        <input type="text" class="form-control" id="nameInput" v-model="nameInput" placeholder="Input Name">
                        <small v-if="!nameInput  && !nameInputValid" class="text-danger">Please fill the input field</small>
                        <small v-else-if="nameInput && !nameRegex.test(nameInput)" class="text-danger">Please fill valid Schedule</small>
                   
                </div>
                </div>
              </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="button" class="btn btn-primary" @click="saveChanges(selectedInfo)">Save changes</button>
            </div>
          </div>
        </div>
      </div>

           <!-- numberModal Modal -->
     <div class="modal fade" id="numberModal" tabindex="-1" aria-labelledby="numberModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header bg-primary text-white">
              <h5 class="modal-title" id="exampleModalLabel">{{ modalTitle }}</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
              <!-- Modal Body Content -->
              <div class="modal-body">  

                <div class="mb-3">

                  <div class="mb-3">
                        <input type="text" class="form-control" id="nameInput" v-model="nameInput" placeholder="Input number">
                        <small v-if="!nameInput  && !nameInputValid" class="text-danger">Please fill the input field</small>
                        <small v-else-if="nameInput && !numberRegex.test(nameInput)" class="text-danger">Please fill valid Limit Schedule</small>
                </div>
                </div>
              </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="button" class="btn btn-primary" @click="numbersaveChanges(selectedInfo)">Save changes</button>
            </div>
          </div>
        </div>
      </div>

                  <!-- Saving Confirmation -->
      <div class="modal fade" id="savingConfirmation" tabindex="-1" aria-labelledby="savingConfirmation" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header bg-primary text-white">
              <h5 class="modal-title" id="savingConfirmationLabel">Are you sure you want to save?</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <!-- Modal Body Content -->
            <div class="modal-body">
              <h4>
                  You cant edit your schedule once it saved.
                </h4>
            </div>
              
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" @click="closeModal()">Close</button>
              <button type="button" class="btn btn-primary" @click="savingConfirmationEvent(selectedInfo)" :disabled="buttonDisabled"> {{ buttonText }} </button>
            </div>
          </div>
        </div>
      </div>

           <!-- Modal -->
     <div class="modal fade" id="limitModal" tabindex="-1" aria-labelledby="limitModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header bg-info text-white">
              <h5 class="modal-title" id="limitModalLabel">Select</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
              <!-- Modal Body Content -->
              <div class="modal-body">
                <div class="mb-3">
                  <button type="button" class="btn btn-success" @click="createEventSelected(selectedInfo)">Create Event</button>
                  <button type="button" class="btn btn-secondary" @click="limitEventSelected(selectedInfo)" >Limit Event</button>
                </div>
              </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
          </div>
        </div>
      </div>

            <!-- Delete -->
            <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header bg-danger text-white ">
              <h5 class="modal-title">Delete</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <h4><p>Are you sure you want to delete?</p></h4>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="button" class="btn btn-danger" @click="deleteItemModal(clickedInfo)">Delete</button>
            </div>
          </div>
        </div>
      </div>


  </div>
</template>



<style lang='css'>


html, body {
    height: 100vh;
    margin: 0;
    padding: 0;
    
   /* overflow: hidden; Disable scrolling of the entire page */
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
    max-height: 100vh; /* Set sidebar height to match viewport height */
  }

  .demo-app-sidebar {
    padding: 10px;
    width: 300px;
    line-height: 1.5;
    /* background: #eaf9ff; */
    background-color: #e6e7e9;
    border-right: 1px solid #d3e2e8;
    /* overflow-y: auto; */
    max-height: 100vh; /* Set sidebar height to match viewport height */
  }

  .demo-app-main {
    flex-grow: 1;
    padding: 3em;
  }

    /* Adjust the height of FullCalendar to fit within the main content area */
  .demo-app-calendar {
      height: calc(100vh - 6em); /* Adjust as needed, considering header/footer heights */
  }

  .fc {
    /* the calendar root */
    /* max-width: 1100px; */
    margin: 0 auto;
    max-height: 100vh; /* Set sidebar height to match viewport height */
  }
  .fc-col-header-cell-cushion{
    text-decoration: none;
    color: black;
  }
  .fc-scrollgrid .fc-daygrid-day-number{
    text-decoration: none;
    color: black;

  }
  .fc-event{
    color: white;
  }

  .fc-list-event-title{
    color: black;
  }

  .fc-list-event-time{
    color: black;
  }
  .date-cell {
  display: flex; /* Use flexbox layout */
  justify-content: flex-start; /* Align content to the start (left) */
}

.cell-content {
  display: flex; /* Use flexbox layout */
  justify-content: flex-start; /* Align content to the start (left) */
  align-items: flex-start; /* Align items to the start (top) */
  margin-right: 50px; /* Adjust margin for spacing */
}

.date {
  display: flex; /* Use flexbox layout */
  justify-content: flex-end; /* Align content to the end (right) */
}

.custom-content{
 font-size: 12px;
}

  </style>


