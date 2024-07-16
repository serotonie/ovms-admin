<script setup>
import "leaflet/dist/leaflet.css";
import { LMap, LTileLayer, LPolyline } from "@vue-leaflet/vue-leaflet";
const props = defineProps({
  trip: Object
})

const latLngs = []

props.trip.waypoints.forEach(e => {
  latLngs.push([e.position_lat, e.position_long])
});

const path = {
  latLngs: latLngs,
  color: "#ff00ff"
}

const center = path

const zoom = 2

</script>
<template>
  <v-card class="mx-auto" width="400">
    <v-img color="surface-variant" :aspect-ratio="16 / 9">
      <l-map ref="map" v-model:zoom="zoom" :center="[47.41322, -1.219482]" :use-global-leaflet="false">
        <l-tile-layer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" layer-type="base" name="OpenStreetMap">
        </l-tile-layer>
        <l-polyline :lat-lngs="path.latLngs" :color="path.color" />
      </l-map>
    </v-img>

    <v-card-text>
      <v-timeline align="start" density="compact">
        <v-timeline-item dot-color="primary" size="x-small">
          <div class="mb-4">
            <div class="font-weight-normal">
              <strong>{{ trip.start_time }}</strong>
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
        <v-timeline-item dot-color="secondary" size="x-small">
          <div class="mb-4">
            <div class="font-weight-normal">
              <strong>{{ trip.stop_time }}</strong>
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
  </v-card>
</template>