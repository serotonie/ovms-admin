<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Breadcrumbs from '@/Components/Breadcrumbs.vue'
import { Head } from '@inertiajs/vue3'
import mqtt from 'mqtt'
import { nextTick, onMounted, onBeforeUnmount, ref } from 'vue'

const props = defineProps({
  vehicle: {
    type: Object,
    required: true,
  },
  mqtt: {
    type: Object,
    required: false,
    default: () => ({}),
  },
})

const breadcrumbs = [
  { title: 'Vehicles', href: route('admin.vehicles.index') },
  { title: props.vehicle.name || 'Vehicle', disabled: true },
]
const mqttClientId = ref(`ovms-admin-cli-${Math.random().toString(16).slice(2)}`)

const terminalOutput = ref([])
const terminalInput = ref('')
const terminalRef = ref(null)
const terminalInputRef = ref(null)
const commandHistory = ref([])
const waitingForResponse = ref(false)
const historyIndex = ref(-1)
const mqttClient = ref(null)
const mqttConnected = ref(false)
const pendingResponse = ref(null)
const isLoading = ref(true)
const loadingMessage = ref('Testing MQTT connection')
const loadingProgress = ref(0)
const loadingSteps = [
  'Testing MQTT connection',
  'Initializing secure session',
  'Validating broker connection',
  'Checking vehicle connection',
]

const addLine = (value, type = 'output') => {
  terminalOutput.value.push({ value, type })
  nextTick(() => {
    if (terminalRef.value) {
      terminalRef.value.scrollTop = terminalRef.value.scrollHeight
    }
  })
}

const getMqttUrl = () => {
  const currentOrigin = window.location.origin
  const pathname = props.mqtt.path || '/mqtt'

  if (currentOrigin) {
    return `${currentOrigin}${pathname}`
  }

  const protocol = props.mqtt.protocol || (window.location.protocol === 'https:' ? 'wss' : 'ws')
  const host = props.mqtt.host || window.location.hostname
  const defaultPort = protocol === 'wss' ? 443 : 80
  const port = props.mqtt.port ?? window.location.port ?? defaultPort
  const path = pathname.startsWith('/') ? pathname : `/${pathname}`

  return `${protocol}://${host}:${port}${path}`
}

const buildTopicClientSegment = () => {
  const vehicleName = props.vehicle.name || props.vehicle.module_username || props.vehicle.module_id || 'vehicle'

  return `ovms-admin-cli-${vehicleName}`
}

const buildTopic = (segment, commandId = null) => {
  const vehiclePrefix = `ovms/${props.vehicle.module_username || 'unknown'}/${props.vehicle.module_id || 'unknown'}`
  const baseTopic = `${vehiclePrefix}/client/${buildTopicClientSegment()}`

  if (!commandId) {
    return `${baseTopic}/${segment}`
  }

  return `${baseTopic}/${segment}/${commandId}`
}

const unsubscribeResponseTopic = (responseTopic) => {
  if (!mqttClient.value || !mqttConnected.value) {
    return
  }

  mqttClient.value.unsubscribe(responseTopic, (error) => {
    if (error) {
      addLine(`MQTT unsubscribe failed: ${error.message || error}`)
    }
  })
}

const publishCommand = (command, options = {}) => {
  if (!mqttClient.value || !mqttConnected.value) {
    addLine('MQTT connection is not ready yet.')
    return
  }

  if (pendingResponse.value) {
    addLine('A command is already waiting for a response.')
    return
  }

  const commandId = `${Date.now().toString(36)}-${Math.random().toString(36).slice(2, 8)}`
  const commandTopic = buildTopic('command', commandId)
  const responseTopic = buildTopic('response', commandId)
  const payload = command
  const isStartupCheck = Boolean(options.startupCheck)

  pendingResponse.value = { commandId, responseTopic, startupCheck: isStartupCheck }
  waitingForResponse.value = true

  mqttClient.value.subscribe(responseTopic, { qos: 0 }, (error) => {
    if (error) {
      pendingResponse.value = null
      waitingForResponse.value = false
      if (isStartupCheck) {
        isLoading.value = false
      }
      addLine(`MQTT subscribe failed: ${error.message || error}`)
      return
    }

    const publishResult = mqttClient.value.publish(commandTopic, payload, { qos: 0, retain: false }, (publishError) => {
      if (publishError) {
        pendingResponse.value = null
        waitingForResponse.value = false
        unsubscribeResponseTopic(responseTopic)
        if (isStartupCheck) {
          isLoading.value = false
        }
        addLine(`MQTT publish failed: ${publishError.message || publishError}`)
      }
    })

    if (publishResult === false) {
      pendingResponse.value = null
      waitingForResponse.value = false
      unsubscribeResponseTopic(responseTopic)
      if (isStartupCheck) {
        isLoading.value = false
      }
      addLine('MQTT publish returned false; the client may be disconnected.')
    }
  })
}

const handleCommand = () => {
  const command = terminalInput.value.trim()

  if (!command) {
    terminalInput.value = ''
    return
  }

  if (waitingForResponse.value) {
    addLine('Waiting for the previous response before sending another command.')
    terminalInput.value = ''
    return
  }

  if (!commandHistory.value.includes(command)) {
    commandHistory.value.push(command)
  }
  historyIndex.value = commandHistory.value.length

  const prompt = `${props.vehicle.module_username || 'vehicle'}@${props.vehicle.module_id || 'cli'}:~$`
  addLine(`${prompt} ${command}`, 'input')
  publishCommand(command)

  terminalInput.value = ''
}

const navigateHistory = (direction) => {
  if (commandHistory.value.length === 0) {
    return
  }

  if (direction === 'up') {
    historyIndex.value = Math.max(0, historyIndex.value - 1)
  } else if (direction === 'down') {
    historyIndex.value = Math.min(commandHistory.value.length, historyIndex.value + 1)
  }

  if (historyIndex.value === commandHistory.value.length) {
    terminalInput.value = ''
    return
  }

  terminalInput.value = commandHistory.value[historyIndex.value]
}

const startLoadingSequence = () => {
  let stepIndex = 0

  const showNextStep = () => {
    if (stepIndex >= loadingSteps.length) {
      window.setTimeout(() => {
        publishCommand('stat', { startupCheck: true })
      }, 500)
      return
    }

    loadingMessage.value = loadingSteps[stepIndex]
    loadingProgress.value = Math.round(((stepIndex + 1) / loadingSteps.length) * 100)
    stepIndex += 1

    window.setTimeout(showNextStep, 700)
  }

  showNextStep()
}

const connectMqtt = () => {
  const mqttUrl = getMqttUrl()
  const client = mqtt.connect(mqttUrl, {
    protocol: props.mqtt.protocol || (window.location.protocol === 'https:' ? 'wss' : 'ws'),
    clientId: mqttClientId.value,
    username: props.mqtt.username || 'admin-client',
    password: props.mqtt.password || 'admin-client-secret',
    clean: true,
  })

  client.on('connect', () => {
    mqttConnected.value = true
    startLoadingSequence()
  })

  client.on('message', (topic, payload) => {
    if (!pendingResponse.value) {
      return
    }

    const pending = pendingResponse.value
    const topicName = topic?.toString() || ''
    const responseTopicBase = pending.responseTopic.replace(/\/response\/.+$/, '/response')
    const matchesResponseTopic = topicName === pending.responseTopic
      || topicName === responseTopicBase
      || topicName.startsWith(`${responseTopicBase}/`)
      || topicName.startsWith(`${pending.responseTopic.split('/response/')[0]}/response/`)

    if (!matchesResponseTopic) {
      return
    }

    const message = payload.toString()

    if (pending.startupCheck) {
      isLoading.value = false
      nextTick(() => {
        addLine('Welcome to the vehicle CLI terminal.')
        addLine('Type "?" to see available commands.')
        nextTick(() => {
          terminalInputRef.value?.focus()
        })
      })
    } else {
      addLine(message)
    }

    pendingResponse.value = null
    waitingForResponse.value = false
    unsubscribeResponseTopic(pending.responseTopic)
  })

  client.on('close', () => {
    mqttConnected.value = false
    addLine('MQTT connection closed.')
  })

  client.on('error', (error) => {
    mqttConnected.value = false
    loadingMessage.value = `MQTT test failed: ${error.message || 'unknown error'}`
    isLoading.value = false
    addLine(`MQTT connection failed: ${error.message || 'unknown error'}`)
  })

  mqttClient.value = client
}

onMounted(() => {
  connectMqtt()
})

onBeforeUnmount(() => {
  mqttClient.value?.end(true)
})
</script>

<template>
  <Head :title="`CLI - ${vehicle.name || 'Vehicle'}`" />
  <AuthenticatedLayout title="Admin">
    <div class="mb-5">
      <h5 class="text-h5 font-weight-bold">Vehicle CLI</h5>
      <Breadcrumbs :items="breadcrumbs" class="pa-0 mt-1" />
    </div>

    <v-card class="pa-4">
      <v-card-title class="px-0">Interactive terminal for {{ vehicle.name }}</v-card-title>
      <v-card-text>
        <div v-if="isLoading" class="terminal-loading-shell">
          <div class="terminal-loading-spinner" aria-hidden="true"></div>
          <div class="terminal-loading-title">{{ loadingMessage }}</div>
          <div class="terminal-loading-subtitle">Initializing the MQTT connection with the vehicle.</div>
          <div class="terminal-loading-progress" aria-hidden="true">
            <div class="terminal-loading-progress-bar" :style="{ width: `${loadingProgress}%` }"></div>
          </div>
          <div class="terminal-loading-progress-text">{{ loadingProgress }}%</div>
        </div>
        <div v-else class="terminal-shell" ref="terminalRef">
          <div v-for="(line, index) in terminalOutput" :key="index" class="terminal-line">
            <span v-if="line.type === 'input'" class="terminal-input-line">{{ line.value }}</span>
            <span v-else>{{ line.value }}</span>
          </div>
          <div v-if="waitingForResponse" class="terminal-line">
            <span class="terminal-waiting">waiting for response...</span>
          </div>
          <div v-else class="terminal-line terminal-prompt-line">
            <span class="terminal-prompt">{{ vehicle.module_username || 'vehicle' }}@{{ vehicle.module_id || 'cli' }}:~$</span>
            <input
              ref="terminalInputRef"
              v-model="terminalInput"
              class="terminal-input"
              autocomplete="off"
              spellcheck="false"
              @keyup.enter="handleCommand"
              @keyup.up.prevent="navigateHistory('up')"
              @keyup.down.prevent="navigateHistory('down')"
            />
          </div>
        </div>
      </v-card-text>
    </v-card>
  </AuthenticatedLayout>
</template>

<style scoped>
.terminal-shell {
  background: #0f172a;
  color: #e2e8f0;
  font-family: 'Fira Code', 'Consolas', monospace;
  padding: 1rem;
  border-radius: 0.75rem;
  min-height: 24rem;
  max-height: 32rem;
  overflow-y: auto;
}

.terminal-line {
  white-space: pre-wrap;
  margin-bottom: 0.35rem;
}

.terminal-prompt-line {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.terminal-prompt {
  color: #34d399;
  flex-shrink: 0;
}

.terminal-input {
  background: transparent;
  border: none;
  color: #f8fafc;
  outline: none;
  flex: 1;
  font: inherit;
}

.terminal-input-line {
  color: #cbd5e1;
}

.terminal-waiting {
  color: #f59e0b;
  font-style: italic;
}

.terminal-loading-shell {
  background: #111827;
  border: 1px solid #1f2937;
  border-radius: 0.75rem;
  min-height: 24rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  color: #e5e7eb;
  text-align: center;
  padding: 2rem;
}

.terminal-loading-spinner {
  width: 2.5rem;
  height: 2.5rem;
  border: 3px solid rgba(255, 255, 255, 0.2);
  border-top-color: #34d399;
  border-radius: 50%;
  animation: terminal-spin 0.9s linear infinite;
}

.terminal-loading-title {
  font-size: 1.05rem;
  font-weight: 600;
  color: #f9fafb;
}

.terminal-loading-subtitle {
  color: #9ca3af;
  max-width: 28rem;
}

.terminal-loading-progress {
  width: min(24rem, 100%);
  height: 0.45rem;
  background: #1f2937;
  border-radius: 999px;
  overflow: hidden;
}

.terminal-loading-progress-bar {
  height: 100%;
  background: linear-gradient(90deg, #34d399, #60a5fa);
  border-radius: inherit;
  transition: width 0.3s ease;
}

.terminal-loading-progress-text {
  color: #cbd5e1;
  font-size: 0.9rem;
}

@keyframes terminal-spin {
  to {
    transform: rotate(360deg);
  }
}
</style>
