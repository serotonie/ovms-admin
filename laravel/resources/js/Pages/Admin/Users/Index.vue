<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Breadcrumbs from '@/Components/Breadcrumbs.vue'
import { Head, Link } from '@inertiajs/vue3'

</script>

<template>

  <Head title="Users" />
  <AuthenticatedLayout title="Admin">
    <div class="mb-5">
      <h5 class="text-h5 font-weight-bold">Users</h5>
      <Breadcrumbs :items="breadcrumbs" class="pa-0 mt-1" />
    </div>
    <v-card class="pa-4">
      <div class="d-flex flex-wrap align-center">
        <v-text-field v-model="search" label="Search" variant="underlined" prepend-inner-icon="mdi-magnify" hide-details
          clearable single-line />
        <v-spacer />
        <Link :href="route('admin.users.invite.create')" as="div">
        <v-btn color="primary" prepend-icon="mdi-plus">Invite new user</v-btn>
        </Link>
      </div>
      <v-data-table-server :items="data.data" :items-length="data.total" :headers="headers" :search="search"
        class="elevation-0" :loading="isLoadingTable" @update:options="loadItems">
        <template #[`item.created_at`]="{ item }">
          <span> {{ new Date(item.created_at).toLocaleString() }}</span>
        </template>
        <template #[`item.role_name`]="{ item }">
          <v-chip :color="item.role_color" variant="flat">{{ item.role_name }}</v-chip>
        </template>
        <template #[`item.action`]="{ item }">
          <Link :href="route('admin.users.edit', item.id)" as="button">
          <v-icon color="warning" icon="mdi-pencil" size="small" />
          </Link>
          <v-icon class="ml-2" color="error" icon="mdi-delete" size="small" @click="deleteItem(item)"
            v-if="can('users all delete') && `${item.id}` != $page.props.auth.user.id" />
        </template>
      </v-data-table-server>
    </v-card>
    <v-row justify="center">
      <v-dialog v-model="deleteDialog" persistent width="auto">
        <v-card>
          <v-card-text>Are you sure you want to delete this user?</v-card-text>
          <v-card-actions>
            <v-spacer />
            <v-btn color="error" text @click="deleteDialog = false">Cancel</v-btn>
            <v-btn color="primary" :loading="isLoading" text @click="submitDelete">Delete</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </v-row>
  </AuthenticatedLayout>
</template>

<script>
export default {
  name: 'UsersIndex',
  props: {
    data: {
      type: Object,
    },
  },
  data() {
    return {
      headers: [
        { title: 'Name', key: 'name' },
        { title: 'Email', key: 'email' },
        { title: 'Created At', key: 'created_at' },
        { title: 'Role', key: 'role_name' },
        { title: 'Actions', key: 'action', sortable: false },
      ],
      breadcrumbs: [
        {
          title: 'Users',
          disabled: true,
        },
      ],
      isLoadingTable: false,
      search: null,
      deleteDialog: false,
      isLoading: false,
      deleteId: null,
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
      this.$inertia.get(route('admin.users.index'), params, {
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
      this.$inertia.delete(route('admin.users.delete', this.deleteId), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
          this.isLoading = false
          this.deleteDialog = false
        },
      })
    },
  }
}
</script>
