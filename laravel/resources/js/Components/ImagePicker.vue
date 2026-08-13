<script setup>
import { ref, computed, watch } from 'vue';
import { Cropper } from 'vue-advanced-cropper';
import 'vue-advanced-cropper/dist/style.css';

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
const cropper = ref(null);
const cropperKey = ref(0);

const imageUrl = computed(() => {
    if (!model.value) return props.defaultImage;
    return URL.createObjectURL(model.value);
});

const selectedFileName = computed(() => {
    if (model.value instanceof File) {
        return model.value.name;
    }

    return 'No image selected';
});

function handleFileImport() {
    isSelecting.value = true;
    uploader.value?.click();
}

function beginEdit() {
    cropperKey.value += 1;
    isEditing.value = true;
}

function handleFileSelection() {
    isSelecting.value = false;
    beginEdit();
}

function handleEdit() {
    beginEdit();
}

function rotateLeft() {
    cropper.value?.rotate(-90);
}

function rotateRight() {
    cropper.value?.rotate(90);
}

function mirrorH() {
    cropper.value?.flip(true, false);
}

function mirrorV() {
    cropper.value?.flip(false, true);
}

async function saveEdit() {
    const result = cropper.value?.getResult();

    if (!result?.canvas) {
        isEditing.value = false;
        return;
    }

    const blob = await new Promise((resolve) => result.canvas.toBlob(resolve, 'image/png'));

    if (!blob) {
        isEditing.value = false;
        return;
    }

    const fileName = model.value instanceof File ? model.value.name : 'edited-image.png';
    model.value = new File([blob], fileName, { type: blob.type || 'image/png' });
    cropperKey.value += 1;
    isEditing.value = false;
}

function cancelEdit() {
    model.value = null;
    cropperKey.value += 1;
    isEditing.value = false;
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
    <div v-if="isEditing" class="mb-5">
        <v-card variant="outlined" class="overflow-hidden">
            <v-card-title class="d-flex align-center justify-space-between">
                <span>Image editor</span>
                <v-chip size="small" color="primary" variant="tonal">
                    {{ selectedFileName }}
                </v-chip>
            </v-card-title>
            <v-divider />
            <v-card-text class="d-flex flex-column align-center gap-4 py-6">
                <div class="d-flex flex-wrap justify-center ga-2 w-100">
                    <v-btn variant="outlined" prepend-icon="mdi-rotate-left" @click="rotateLeft">Rotate left</v-btn>
                    <v-btn variant="outlined" prepend-icon="mdi-rotate-right" @click="rotateRight">Rotate right</v-btn>
                    <v-btn variant="outlined" prepend-icon="mdi-flip-horizontal" @click="mirrorH">Mirror H</v-btn>
                    <v-btn variant="outlined" prepend-icon="mdi-flip-vertical" @click="mirrorV">Mirror V</v-btn>
                </div>
                <div class="w-100" style="max-width: 720px;">
                    <Cropper
                        :key="cropperKey"
                        ref="cropper"
                        :src="imageUrl"
                        :stencil-props="{ aspectRatio: 16 / 9, movable: true, resizable: true }"
                        class="rounded-lg overflow-hidden"
                        style="width: 100%; min-height: 420px; background: #f5f5f5;"
                    />
                </div>
            </v-card-text>
            <v-card-actions class="justify-end px-4 pb-4">
                <v-btn variant="text" @click="cancelEdit">Cancel</v-btn>
                <v-btn color="primary" prepend-icon="mdi-content-save" @click="saveEdit">Save</v-btn>
            </v-card-actions>
        </v-card>
    </div>
    <div v-else>
        <v-hover :disabled="false" v-slot="{ isHovering, props: hoverProps }">
            <v-img class="mb-5" v-bind="hoverProps" color="surface-variant" :src="imageUrl" :aspect-ratio="16 / 9" cover>
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
    <v-file-input v-model="model" accept="image/*" ref="uploader" class="d-none" @change="handleFileSelection" />
</template>
