<script setup>
import NavigationMenu from '@/Components/NavigationMenu.vue'
import { Link } from '@inertiajs/vue3'
</script>

<template>
  <v-app class="bg-grey-lighten-4">
    <v-navigation-drawer v-model="drawer" :rail="rail" permanent>
      <v-list>
        <v-list-item
          :title="$page.props.auth.user.name"
          :subtitle="$page.props.auth.user.roles[0].name"
        >
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
      <v-app-bar-nav-icon v-if="$vuetify.display.mobile" @click.stop="drawer = !drawer" />
      <v-app-bar-nav-icon v-else @click.stop="rail = !rail" />
      <v-toolbar-title text="OVMS Admin" />
    </v-app-bar>
    <v-main>
      <v-container>
        <slot />
      </v-container>
    </v-main>
  </v-app>
</template>

<script>
import { useToast } from 'vue-toastification'

export default {
  data() {
    return {
      drawer: false,
      rail: false,
    }
  },
  mounted() {
    this.drawer = !this.$vuetify.display.mobile
    const toast = useToast()
    const flash = this.$page.props.flash
    if (flash.info) {
      toast.info(flash.info)
    } else if (flash.success) {
      toast.success(flash.success)
    } else if (flash.warning) {
      toast.warning(flash.warning)
    } else if (flash.error) {
      toast.error(flash.error)
    }
  },
}
</script>