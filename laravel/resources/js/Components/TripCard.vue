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
  vehicles: Array,
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
  category_id: props.trip.category_id,
  user_id: props.trip.user_id
})

function submit() {
  console.log('submit')
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
    <v-card-title>{{ vehicles.find(o => o.id === trip.vehicle_id).name }}</v-card-title>
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
      <div width="500">
        <v-form @submit.prevent="submit">
          <v-autocomplete variant="underlined" v-model="form.category_id" label="Category"
            :error-messages="form.errors.category_id" :items="props.categories" item-title="name" item-value="id"
            append-icon="mdi-plus" @click:append="addCategoryHandler" />
          <v-autocomplete variant="underlined" v-model="form.user_id" label="User" :error-messages="form.errors.user_id"
            :items="props.system_users" item-title="name" item-value="id" />
        </v-form>
      </div>
    </v-card-actions>
  </v-card>
</template>