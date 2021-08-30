<template>
  <div class="relative flex items-center">
    <div class="flex items-center h-5">
      <input type="checkbox" class="focus:ring-indigo-500 h-5 w-5 text-indigo-600 border-gray-300 rounded" v-model="task.is_done" />
    </div>
    <div class="ml-4 text-lg w-full">
      <JetInput v-if="editing" ref="input" v-model="task.name" @keyup.enter="saveName" class="block w-full" @blur="editing = false" />
      <label v-else @click="edit" class="w-full block font-medium text-gray-700 cursor-text">{{ task.name }}</label>
    </div>
    <span class="ml-auto inline-block py-1 px-3 text-xs cursor-pointer" @click="confirmingDelete = true">
      <XIcon class="h-4 w-4 text-gray-300" />
    </span>
    <JetConfirmationModal :show="confirmingDelete" @close="confirmingDelete = false">
      <template #title>
        Delete Task
      </template>

      <template #content>
        Are you sure you would like to delete this task?
      </template>

      <template #footer>
          <jet-secondary-button @click="confirmingDelete = false">
              Cancel
          </jet-secondary-button>

          <jet-danger-button class="ml-2" @click="deleteTask">
            Delete
          </jet-danger-button>
      </template>
    </JetConfirmationModal>
  </div>		
</template>

<script>
import JetInput from '@/Jetstream/Input.vue'
import JetConfirmationModal from '@/Jetstream/ConfirmationModal.vue'
import JetSecondaryButton from '@/Jetstream/SecondaryButton.vue'
import JetDangerButton from '@/Jetstream/DangerButton.vue'
import { XIcon } from '@heroicons/vue/solid'

export default {
  components: {
    JetInput,
    XIcon,
    JetConfirmationModal,
    JetSecondaryButton,
    JetDangerButton,
  },

  emits: ['new-minute', 'snapshots'],

  props: {
    task: {
      type: Object,
      required: true,
    },
  },

  data () {
    return {
      editing: false,
      confirmingDelete: false,
    }
  },

  watch: {
    'task.is_done' (newValue) {
      // sync in the background
      axios.put(`/tasks/${this.task.id}`, { is_done: newValue, name: this.task.name })
        .then(response => this.$emit('snapshots', response.data))
    },
  },

  methods: {
    edit () {
      this.editing = true
      this.$nextTick(() => this.$refs.input.focus())
    },

    saveName () {
      // update UI immediately, then sync to the DB in the background
      this.editing = false
      axios.put(`/tasks/${this.task.id}`, { name: this.task.name })
        .then(response => this.$emit('snapshots', response.data))
    },

    deleteTask () {
      this.$emit('delete-task')
    },
  },
}
</script>
