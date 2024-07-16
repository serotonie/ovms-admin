<script setup>
import { ref, computed, watch } from 'vue';
import VuePictureCropper, { cropper } from 'vue-picture-cropper';

const props = defineProps({
    defaultImage: {
        type: String,
        required: true
    }
})

const model = defineModel({ required: true })

const isSelecting = ref(false);
const isEditing = ref(false);
const uploader = ref(null);

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

function scale(x, y) {
    cropper.scale(x, y)
}

function rotate(angle) {
    cropper.rotate(angle);
}

async function crop() {
    model.value = await cropper.getBlob()
    isEditing.value = false
}

const emit = defineEmits(['beforeEditing', 'afterEditing'])

watch(isEditing, (newValue) => {
    if (newValue) {
        emit('beforeEditing')
    }
    else {
        emit('afterEditing')
    }
})
</script>


<template>
    <div v-if="isEditing">
        <VuePictureCropper :boxStyle="{
            width: '100%',
            height: '100%',
            backgroundColor: '#f8f8f8',
            margin: 'auto',
        }" :img="imageUrl" :options="{
            viewMode: 1,
            dragMode: 'crop',
            aspectRatio: 16 / 9,
        }" />
        <div class="mb-5">
            <v-btn prepend-icon="mdi-rotate-left" @click="rotate(-90)">-90°</v-btn>
            <v-btn prepend-icon="mdi-rotate-right" @click="rotate(90)">90°</v-btn>
            <v-btn prepend-icon="mdi-flip-horizontal" @click="scale(-1, 1)">Horizontal</v-btn>
            <v-btn prepend-icon="mdi-flip-vertical" @click="scale(1, -1)">Vertical</v-btn>
            <v-btn prepend-icon="mdi-content-save" @click="crop()">Save</v-btn>
        </div>
    </div>
    <div v-else>
        <v-hover :disabled="false" v-slot="{ isHovering, props }">
            <v-img class="mb-5" v-bind="props" color="surface-variant" :src="imageUrl" :aspect-ratio="16 / 9" cover>
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
