<script setup>
import { Link } from '@inertiajs/vue3'
import navigation from '@/Configs/navigation'
import usePermissions from '../../../vendor/wijzijnweb/laravel-inertia-permissions/resources/js/Uses/usePermissions.ts';

const { can } = usePermissions()
</script>

<template>
  <v-list nav>
    <template v-for="(item, key) in navigation.items">
      <VListSubheader>{{ key }}</VListSubheader>
      <Link v-for="(item, key) in item" :key="key" :href="item.to" as="div">
        <v-list-item
          :prepend-icon="item.icon"
          :title="item.title"
          :exact="item.exact"
          link
          :class="{ 'v-list-item--active': $page.url.startsWith(item.to) }"
          v-if="can(item.permissions)|item.permissions===''"
        />
      </Link>
    </template>
  </v-list>
</template>
