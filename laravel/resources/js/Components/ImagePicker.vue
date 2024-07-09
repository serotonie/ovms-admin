<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    defaultImage: {
        type: String,
        required: true
    }
})

const model = defineModel()

const isSelecting = ref(false);
const uploader = ref();
//const image = ref()

const imageUrl = computed(() => {
    if (!model.value) return props.defaultImage;
    return URL.createObjectURL(model.value);
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
        <v-hover v-slot="{ isHovering, props }">
            <v-img v-bind="props" lazy-src="/car_default.png" :src="imageUrl">
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
                </v-overlay>
            </v-img>
        </v-hover>
    </div>
    <v-file-input v-model="model" accept="image/*" ref="uploader" class="d-none" />

</template>
