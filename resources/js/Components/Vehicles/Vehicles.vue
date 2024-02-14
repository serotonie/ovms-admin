<script setup>
import VehicleCard from './VehicleCard.vue';
</script>

<template>
<v-container>
    <v-row align="center" justify="center">
        <v-col
            cols="auto"
            v-for="(n, index) in 12"
        >
            <VehicleCard></VehicleCard>
        </v-col>
    </v-row>
</v-container>
</template>
