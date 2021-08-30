<template>
  <div class="w-full"><canvas id="burndown"></canvas></div>
</template>

<script>
import dayjs from 'dayjs'
import { Chart, registerables } from 'chart.js'
Chart.register(...registerables)

export default {
  props: {
    snapshots: {
      type: Array,
      required: true,
    },
  },

  data () {
    return {
      chart: null,
      interval: null,
    }
  },

  computed: {
    reversedSnapshots () {
      return [...this.snapshots].reverse()
    },
  },

  watch: {
    reversedSnapshots () {
      this.chart.destroy()
      this.setupChart(true)
    },
  },

  mounted () {
    this.setupChart()

    // fetch new burndown data every minute
    this.interval = setInterval(() => {
      this.$emit('new-minute')
    }, 60000)
  },

  methods: {
    generateChartData () {
      const chartData = []
      for (let minutesAgo = 59; minutesAgo >= 0; minutesAgo--) {
        const snapshot = this.getSnapshotForMinute(minutesAgo)
        if (snapshot) {
          chartData.push((snapshot.num_remaining / snapshot.num_tasks) * 100)
        } else {
          chartData.push(null)
        }
      }

      return chartData
    },

    getSnapshotForMinute (minutesAgo) {
      for (let index in this.reversedSnapshots) {
        const currentSnapshot = this.reversedSnapshots[index]
        if (dayjs(currentSnapshot.minute).isBefore(dayjs().subtract(minutesAgo, 'minute'))) {
          return currentSnapshot
        }
      }
    },

    getPercent (numerator, denominator) {
      return (numerator / denominator) * 100
    },

    setupChart (dontAnimate = false) {
      let speedCanvas = document.getElementById('burndown')

      const labels = []
      for (let i = 60; i > 0; i--) {
        labels.push(i)
      }

      const data = {
        labels,
        datasets: [
          {
            label: "Burndown",
            data: this.generateChartData(),
            fill: false,
            borderColor: "#EE6868",
            backgroundColor: "#EE6868",
            lineTension: 0,
          },
        ]
      }

      let options = {
        legend: {
          display: true,
          position: 'top',
          labels: {
            boxWidth: 80,
            fontColor: 'black'
          }
        },
        scales: {
          x: {
            title: {
              display: true,
              text: '# of minutes ago',
              font: {
                size: 16,
              }
            }
          },
          y: {
            title: {
              display: true,
              text: '% of tasks remaining',
              font: {
                size: 16,
              }
            },
            min: -10,
            max: 110,
          }
        }
      }

      if (dontAnimate) {
        options.animation = false
      }

      this.chart = new Chart(speedCanvas, {
        type: 'line',
        data,
        options,
      })
    },
  },

  beforeUnmount () {
    clearInterval(this.interval)
  },
}
</script>

<style>

</style>