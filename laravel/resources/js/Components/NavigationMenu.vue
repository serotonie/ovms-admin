<script setup>
import { Link } from '@inertiajs/vue3'
import navigation from '@/Configs/navigation'

</script>

<template>
  <v-list nav>
    <template v-for="(item, key) in navigation.items">
      <VListSubheader>{{ key }}</VListSubheader>
      <Link v-for="(item, key) in item" :key="key" :href="item.to" as="div">
      <v-list-item :prepend-icon="item.icon" :title="item.title" :exact="item.exact" link
        :class="{ 'v-list-item--active': $page.url.startsWith(item.to) }" />
      </Link>
    </template>
  </v-list>
</template>
