<template>
      <v-card
        class="mx-auto pa-3 mt-5"
        max-width="344"
        title="Avatars"
        subtitle="prepend and append"
      >
        <template v-slot:prepend>
          <v-avatar color="blue-darken-2">
            <v-icon icon="mdi-alarm"></v-icon>
          </v-avatar>
        </template>
        <v-card-text>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod.</v-card-text>
      </v-card>
</template>
