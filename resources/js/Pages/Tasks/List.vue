<template>
  <app-layout title="Dashboard">
    <div class="py-12">
      <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-16">
          <BurndownChart class="mb-6" :snapshots="snapshots" @new-minute="getSnapshots" />

          <fieldset v-if="tasks.length > 0" class="space-y-3 m-5">
            <!-- <legend class="text-lg font-medium text-gray-900">{{ numTasksRemaining }} / {{ numTasks }} tasks remaining</legend> -->
            <TaskItem
              v-for="(task, index) in tasks" :key="task.id"
              :task="task"
              class="relative flex items-start"
              @delete-task="deleteTask(task, index)"
              @snapshots="updateSnapshots"
            />
          </fieldset>
          <div class="mt-5 col-span-6 sm:col-span-4">
            <jet-label for="name" value="Add New Task" />
            <jet-input id="name" type="text" class="mt-1 block w-full" v-model="newTask" @keyup.enter="createTask" />
            <jet-input-error :message="errors.name" class="mt-2" />
          </div>

          <jet-button class="mt-2" @click="createTask">
            Save
          </jet-button>
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script>
  import AppLayout from '@/Layouts/AppLayout.vue'
  import TaskItem from '@/Components/TaskItem.vue'
  import BurndownChart from '@/Components/BurndownChart.vue'
  import JetLabel from '@/Jetstream/Label.vue'
  import JetInput from '@/Jetstream/Input.vue'
  import JetInputError from '@/Jetstream/InputError.vue'
  import JetButton from '@/Jetstream/Button.vue'

  export default {
    components: {
      TaskItem,
      BurndownChart,
      JetInput,
      JetLabel,
      JetInputError,
      JetButton,
    },

    layout: AppLayout,

    props: {
      tasks: {
        type: Array,
        required: true,
      },
      burndown: {
        type: Array,
        required: true,
      }
    },

    data () {
      return {
        newTask: '',
        errors: {},
        snapshots: this.burndown,
      }
    },

    computed: {
      numTasks () {
        return this.tasks.length
      },

      numTasksRemaining () {
        return this.tasks.filter(task => ! task.is_done).length
      },
    },

    methods: {
      createTask () {
        // create a temporary ID
        const tempId = 'temp-' + Math.floor(Math.random() * 1000)

        // save the original name in case an error occurs and we need to reset the state
        const name = this.newTask

        // update the UI immediately so the user feels that the app is fast
        this.tasks.push({
          id: tempId,
          name,
        })
        this.newTask = ''

        // sync the change to the background
        axios.post('/tasks', { name })
          .then(response => {
            // update the temporary object with the real object from the database
            const tempIndex = this.tasks.findIndex(task => task.id == tempId)
            this.tasks[tempIndex] = response.data.task
            this.snapshots = response.data.burndown
          })
          .catch(error => {
            // something went wrong, remove the temporary object and put the name back in the field
            const tempIndex = this.tasks.findIndex(task => task.id == tempId)
            this.newTask = name
            this.tasks.splice(tempIndex, 1)
          })
      },

      updateSnapshots (value) {
        console.log('update', value)
        this.snapshots = value
      },

      getSnapshots () {
        axios.get('/burndown')
          .then(response => {
            this.snapshots = response.data
          })
      },

      deleteTask (task, index) {
        // update UI immediately
        this.tasks.splice(index, 1)

        // sync in the background
        axios.delete(`/tasks/${task.id}`)
          .then(response => {
            this.snapshots = response.data
          })
          .catch(error => {
            // something went wrong, put the task back
            this.tasks.splice(index, 0, task)
          })
      },
    },
  }
</script>
