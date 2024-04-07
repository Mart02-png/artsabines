<template>
  <div>
    <Datepicker
      uid="auto"
      time-picker
      v-model="date"
      :max-time="{ hours: 22, minutes: 40 }"
      :min-time="{ hours: 7, minutes: 10 }"
      auto-apply
    />
    <hr />
    <pre>
      The min-time / max-time is set and also a default time.
      
      Now when I click on the time-picker,
      the value displayed is current time of the browser
      not 10:15 as set by default.
      
      version: 2.3.3 of vue3-date-time-picker
    </pre>
  </div>
</template>

<script>
import { ref } from "vue";
import Datepicker from 'vue3-timepicker';

export default {
  components: {
    Datepicker,
  },
  setup() {
    // Initialize the date ref with the default time
    const date = ref({
      year: new Date().getFullYear(),
      month: new Date().getMonth() + 1,
      day: new Date().getDate(),
      hours: 10,
      minutes: 15,
    });

    return {
      date,
    };
  },
};
</script>
