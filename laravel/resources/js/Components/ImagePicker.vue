<script setup>
import { ref, computed } from 'vue';
import VueCropper from 'vue-cropperjs';
import 'cropperjs/dist/cropper.css';

const props = defineProps({
    defaultImage: {
        type: String,
        required: true
    }
})

const model = defineModel()

const isSelecting = ref(false);
const isEditing = ref(false);
const uploader = ref();

const imageUrl = computed(() => {
    if (!model.value) return props.defaultImage;
    return URL.createObjectURL(model.value);
});

function handleFileImport() {
    isSelecting.value = true;

    window.addEventListener('focus', () => {
        isSelecting.value = false
        isEditing.value = true;
    }, { once: true });

    uploader.value.click();
};

function handleEdit() {
    isEditing.value = true;
}
</script>
<template>
    <div v-if="isEditing">
        <vue-cropper :aspect-ratio="16 / 9" :src="imageUrl" />
        <div class="mb-5">
            <v-btn></v-btn>
        </div>
    </div>
    <div v-else>
        <v-hover :disabled="false" v-slot="{ isHovering, props }">
            <v-img class="mb-5" v-bind="props" lazy-src="/car_default.png" :src="imageUrl" :aspect-ratio="16 / 9" cover>
                <template v-slot:placeholder>
                    <div class="d-flex align-center justify-center fill-height">
                        <v-progress-circular color="grey-lighten-4" indeterminate></v-progress-circular>
                    </div>
                </template>
                <v-overlay :model-value="isHovering" class="align-center justify-center" scrim="black" contained>
                    <v-btn :loading="isSelecting" @click="handleFileImport" flat color="transparent" size="x-large"
                        v-tooltip="'Select an image for this vehicle'">
                        <v-icon size="x-large" color="white">mdi-image-plus</v-icon>
                    </v-btn>
                    <v-btn :loading="isEditing" @click="handleEdit" flat color="transparent" size="x-large"
                        v-tooltip="'Edit the image for this vehicle'">
                        <v-icon size="x-large" color="white">mdi-crop-rotate</v-icon>
                    </v-btn>
                </v-overlay>
            </v-img>
        </v-hover>
    </div>
    <v-file-input v-model="model" accept="image/*" ref="uploader" class="d-none" />

</template>
