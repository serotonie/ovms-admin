<script setup>
import { ref, computed } from 'vue';

const isSelecting = ref(false);
const uploader = ref();
const image = ref()

const imageUrl = computed(() => {
    if (!image.value) return "https://ev-database.org/img/auto/Volkswagen_e-Up-2019/Volkswagen_e-Up-2019-01.jpg";
    return URL.createObjectURL(image.value);
});

function handleFileImport() {
    isSelecting.value = true;

    window.addEventListener('focus', () => {
        isSelecting.value = false
    }, { once: true });

    uploader.value.click();
};
</script>
<template>
    <div>
        <v-card class="mx-auto" max-width="344">
            <v-hover v-slot="{ isHovering, props }">
                <v-img v-bind="props" :src="imageUrl">
                    <v-overlay :model-value="isHovering" class="align-center justify-center" scrim="black" contained>
                        <v-btn :loading="isSelecting" @click="handleFileImport" flat color="transparent" size="x-large"
                            v-tooltip="'Select an image for this vehicle'">
                            <v-icon size="x-large" color="white">mdi-image-plus</v-icon>
                        </v-btn>
                    </v-overlay>
                </v-img>
            </v-hover>
        </v-card>
    </div>
    <v-file-input v-model="image" accept="image/*" ref="uploader" class="d-none" />

</template>
