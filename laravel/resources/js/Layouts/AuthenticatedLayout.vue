<script setup>
import NavigationMenu from '@/Components/NavigationMenu.vue'
import Breadcrumbs from '@/Components/Breadcrumbs.vue'
import { Link, usePage } from '@inertiajs/vue3'
import { useToast } from 'vue-toastification';
import { onMounted, ref } from 'vue'
import { useDisplay } from 'vuetify/lib/framework.mjs';

const props = defineProps({
  title: String
})

const page = usePage()
const display = useDisplay()

const drawer = ref(false)
const rail = ref(false)

onMounted(() => {
  drawer.value = !display.mobile
  const toast = useToast()
  const flash = page.props.flash
  if (flash.info) {
    toast.info(flash.info)
  } else if (flash.success) {
    toast.success(flash.success)
  } else if (flash.warning) {
    toast.warning(flash.warning)
  } else if (flash.error) {
    toast.error(flash.error)
  }
})
</script>

<template>
  <v-app class="bg-grey-lighten-4">
    <v-navigation-drawer v-model="drawer" :rail="rail" permanent>
      <v-list>
        <v-list-item :title="page.props.auth.user.name" :subtitle="page.props.auth.user.roles[0].name">
          <template v-slot:append>
            <v-menu>
              <template v-slot:activator="{ props }">
                <v-btn icon="mdi-menu-down" variant="text" v-bind="props"></v-btn>
              </template>
              <v-list>
                <Link href="/logout" method="post" as="div">
                <v-list-item prepend-icon="mdi-exit-to-app" title="Log Out" link />
                </Link>
              </v-list>
            </v-menu>
          </template>
        </v-list-item>
      </v-list>
      <v-divider />
      <NavigationMenu />
    </v-navigation-drawer>
    <v-app-bar color="primary">
      <v-app-bar-nav-icon v-if="display.mobile" @click.stop="drawer = !drawer" />
      <v-app-bar-nav-icon v-else @click.stop="rail = !rail" />
      <v-toolbar-title :text="title" /> </v-app-bar>
    <v-main>
      <v-container>
        <slot />
      </v-container>
    </v-main>
  </v-app>
</template>
