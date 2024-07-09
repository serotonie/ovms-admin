<script setup>
const props = defineProps({
    vehicle: Object
})
</script>
<template>
    <v-card class="mx-auto" width="400">
        <v-img color="surface-variant" height="200" :src="vehicle.picture" cover />
        <v-card-title>
            {{ vehicle.name }}
        </v-card-title>
    </v-card>
</template>