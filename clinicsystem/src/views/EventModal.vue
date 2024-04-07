<template>
    <!-- Button trigger modal -->
    <button type="button" class="btn btn-primary" @click="openModal">
      Launch demo modal
    </button>
  
    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            ...
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary">Save changes</button>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import { ref } from 'vue';
  import Modal from 'bootstrap/js/dist/modal';
  
  export default {
    setup() {
      const modal = ref(null);
  
      const openModal = () => {
        if (!modal.value) {
          modal.value = new Modal(document.getElementById('exampleModal'));
        }
        modal.value.show();
      };
  
        // Method to handle save changes click
        const saveChanges = () => {
        console.log("Successfully save");
        // You can add your save logic here
        };
      return {
        openModal,
      };
    }
  };
  </script>
  