<script setup>
import "leaflet/dist/leaflet.css";
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import { useTheme } from "vuetify/lib/framework.mjs";
import { LMap, LTileLayer, LPolyline, LCircleMarker } from "@vue-leaflet/vue-leaflet";
import moment from "moment";

const map = ref(null)

const props = defineProps({
  trip: Object,
  vehicle: Object,
  categories: Array
})

const latLngs = []
props.trip.waypoints.forEach(e => {
  latLngs.push([e.position_lat, e.position_long])
});

const path = {
  latLngs: latLngs,
  color: "#ff00ff"
}

const startMarker = {
  latLng: path.latLngs[0],
  color: useTheme().current.value.colors.primary
}

const stopMarker = {
  latLng: path.latLngs[path.latLngs.length - 1],
  color: useTheme().current.value.colors.secondary
}

function pathReady(e) {
  if (Object.keys(e.getBounds()).length != 0) {
    map.value.leafletObject.fitBounds(e.getBounds())
  }
}

const form = useForm({
  vehicle_id: props.vehicle.id,
  category_id: props.trip.category_id,
  user_id: props.trip.user_id
})

function submit() {
  form.patch(route('trips.update', props.trip.id))
}

function cancel() {
  form.reset()
}

function addCategoryHandler() {
  console.log('addCategory')
}

</script>

<template>
  <v-card class="mx-auto fill-height" min-width="250" max-width="400">
    <v-img color="surface-variant" :aspect-ratio="16 / 9">
      <l-map v-if="path.latLngs.length != 0" ref="map" :options="{
        scrollWheelZoom: false
      }" :use-global-leaflet="false">
        <l-tile-layer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" layer-type="base" name="OpenStreetMap" />
        <l-polyline @ready="pathReady" :lat-lngs="path.latLngs" :color="path.color" />
        <l-circle-marker :lat-lng="startMarker.latLng" :color="startMarker.color" :fill-opacity="1" :radius="4" />
        <l-circle-marker :lat-lng="stopMarker.latLng" :color="stopMarker.color" :fill-opacity="1" :radius="4" />
      </l-map>
    </v-img>
    <v-card-title>{{ vehicle.name }}</v-card-title>
    <v-card-subtitle>{{ vehicle.ownership_level }}</v-card-subtitle>
    <v-card-text>
      <v-timeline align="start" density="compact">
        <v-timeline-item height="160" dot-color="primary" size="x-small">
          <div class="mb-4">
            <div class="font-weight-normal">
              <strong>{{ moment(trip.start_time).format('L') }}</strong> {{ moment(trip.start_time).format('LT') }}
              <v-divider class="mb-4" />
              <p>
                {{ trip.start_road }} {{ trip.start_house_number }}
              <p>
                {{
                  trip.start_postcode }} {{ trip.start_village }}
              </p>
              <p>{{ trip.start_country }}</p>
              </p>
            </div>
          </div>
        </v-timeline-item>
        <v-timeline-item height="160" dot-color="secondary" size="x-small">
          <div class="mb-4">
            <div class="font-weight-normal">
              <strong>{{ moment(trip.stop_time).format('L') }}</strong> {{ moment(trip.stop_time).format('LT') }}
              <v-divider class="mb-4" />
              <p>{{ trip.stop_road }} {{ trip.stop_house_number }} </p>
              <p>
                {{
                  trip.stop_postcode }} {{ trip.stop_village }}
              </p>
              <p> {{ trip.stop_country }}</p>
            </div>
          </div>
        </v-timeline-item>
      </v-timeline>
    </v-card-text>
    <v-card-actions>
      <div class="w-100">
        <v-form @submit.prevent="submit">
          <v-row justify="space-between">
            <v-col cols="9">
              <v-autocomplete variant="underlined" v-model="form.category_id" label="Category"
                :error-messages="form.errors.category_id" :items="props.categories" item-title="name" item-value="id"
                append-icon="mdi-plus" @click:append="addCategoryHandler" />
              <v-autocomplete variant="underlined" v-model="form.user_id" label="User"
                :error-messages="form.errors.user_id" :items="vehicle.users" item-title="name"
                item-value="pivot.user_id" />
            </v-col>
            <v-col class="d-flex flex-column align-self-center align-end">
              <v-row>
                <v-btn @click="cancel" icon="mdi-cancel" />
              </v-row>
              <v-row>
                <v-btn type="submit" color="primary" icon="mdi-content-save" />
              </v-row>
            </v-col>
          </v-row>
        </v-form>
      </div>
    </v-card-actions>
  </v-card>
</template>