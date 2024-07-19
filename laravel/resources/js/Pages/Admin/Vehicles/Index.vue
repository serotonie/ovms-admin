<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Breadcrumbs from '@/Components/Breadcrumbs.vue'
import { Head, Link } from '@inertiajs/vue3'
import usePermissions from '@/../../vendor/wijzijnweb/laravel-inertia-permissions/resources/js/Uses/usePermissions.ts';
import moment from 'moment';
const { can } = usePermissions()
</script>

<template>

  <Head title="Vehicle" />
  <AuthenticatedLayout title="Admin">
    <div class="mb-5">
      <h5 class="text-h5 font-weight-bold">Vehicle</h5>
      <Breadcrumbs :items="breadcrumbs" class="pa-0 mt-1" />
    </div>
    <v-card class="pa-4">
      <div class="d-flex flex-wrap align-center">
        <v-text-field v-model="search" label="Search" variant="underlined" prepend-inner-icon="mdi-magnify" hide-details
          clearable single-line />
        <v-spacer />
        <Link :href="route('admin.vehicles.create')" as="div" v-if="can('vehicles all create')">
        <v-btn color="primary" prepend-icon="mdi-plus">New Vehicle</v-btn>
        </Link>
      </div>
      <v-data-table-server :items="data.data" :items-length="data.total" :headers="headers" :search="search"
        class="elevation-0" :loading="isLoadingTable" @update:options="loadItems">
        <template #[`item.owner`]="{ item }">
          <Link :href="route('admin.users.show', item.owner.id)" as="button">
          <v-chip prepend-icon="mdi-eye">{{ item.owner.name }}</v-chip>
          </Link>
        </template>
        <template #[`item.main_user`]="{ item }">
          <Link :href="route('admin.users.show', item.main_user.id)" as="button">
          <v-chip prepend-icon="mdi-eye">{{ item.main_user.name }}</v-chip>
          </Link>
        </template>
        <template #[`item.users`]="{ item }">
          <v-chip-group v-for="user in item.users">
            <Link :href="route('admin.users.show', user.id)" as="button">
            <v-chip prepend-icon="mdi-eye" class="ma-1">{{ user.name }}</v-chip>
            </Link>
          </v-chip-group>
        </template>
        <template #[`item.last_seen`]="{ item }">
          <span>{{ item.last_seen ? moment(item.last_seen).fromNow() : 'never' }}</span>
        </template>
        <template #[`item.action`]="{ item }">
          <Link :href="route('admin.vehicles.edit', item.id)" as="button" v-if="can('vehicles all update')">
          <v-icon color="warning" icon="mdi-pencil" size="small" />
          </Link>
          <v-icon class="ml-2" color="error" icon="mdi-delete" size="small" @click="deleteItem(item)"
            v-if="can('vehicles all delete')" />
          <v-icon class="ml-2" color="werning" icon="mdi-restore" size="small" @click="resetItem(item)"
            v-if="can('vehicles all delete') && can('vehicles all update')" />
        </template>
      </v-data-table-server>
    </v-card>
    <v-row justify="center">
      <v-dialog v-model="deleteDialog" persistent width="auto">
        <v-card>
          <v-card-text>Are you sure you want to delete this vehicle?</v-card-text>
          <v-card-actions>
            <v-spacer />
            <v-btn color="error" :disabled="isLoading" text @click="deleteDialog = false">Cancel</v-btn>
            <v-btn color="primary" :loading="isLoading" text @click="submitDelete">Delete</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
      <v-dialog v-model="resetDialog" persistent width="auto">
        <v-card>
          <v-card-text>Are you sure you want to reset this vehicle?</v-card-text>
          <v-card-actions>
            <v-spacer />
            <v-btn color="error" :disabled="isLoading" text @click="resetDialog = false">Cancel</v-btn>
            <v-btn color="primary" :loading="isLoading" text @click="submitReset">Reset</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </v-row>
  </AuthenticatedLayout>
</template>

<script>
export default {
  name: 'VehiclesIndex',
  props: {
    data: {
      type: Object,
    },
  },
  data() {
    return {
      headers: [
        { title: 'Name', key: 'name' },
        { title: 'Owner', key: 'owner' },
        { title: 'Main User', key: 'main_user' },
        { title: 'Users', key: 'users' },
        { title: 'Last Seen', key: 'last_seen' },
        { title: 'Actions', key: 'action', sortable: false },
      ],
      breadcrumbs: [
        {
          title: 'Vehicles',
          disabled: true,
        },
      ],
      isLoadingTable: false,
      search: null,
      deleteDialog: false,
      isLoading: false,
      deleteId: null,
      resetDialog: false
    }
  },
  methods: {
    loadItems({ page, itemsPerPage, sortBy, search }) {
      this.isLoadingTable = true
      var params = {
        page: page,
        limit: itemsPerPage,
        sort: sortBy[0],
      }
      if (search) {
        params.search = search
      }
      this.$inertia.get(route('admin.vehicles.index'), params, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
          this.isLoadingTable = false
        },
      })
    },
    deleteItem(item) {
      this.deleteId = item.id
      this.deleteDialog = true
    },
    submitDelete() {
      this.isLoading = true
      this.$inertia.delete(route('admin.vehicles.delete', this.deleteId), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
          this.isLoading = false
          this.deleteDialog = false
        },
      })
    },
    resetItem(item) {
      this.resetId = item.id
      this.resetDialog = true
    },
    submitReset() {
      this.isLoading = true
      this.$inertia.delete(route('admin.vehicles.delete', this.resetId), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
          this.isLoading = false
          this.resetDialog = false
          this.$inertia.get(route('admin.vehicles.create'))
        },
      })
    },
  }
}
</script>
