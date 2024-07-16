export default {
  items: {
    user: [
      {
        title: 'Dashboard',
        icon: 'mdi-view-dashboard',
        to: route('dashboard'),
        permissions: ''
      },
      {
        title: 'My Vehicles',
        icon: 'mdi-car-multiple',
        to: route('vehicles.index'),
        permissions: ''
      },
      {
        title: 'My Trips',
        icon: 'mdi-map-marker-path',
        to: route('trips.index'),
        permissions: ''
      }
    ],
    admin: [
      {
        title: 'Users',
        icon: 'mdi-account-group',
        to: route('admin.users.index'),
        permissions: 'users all read'
      },
      {
        title: 'Vehicles',
        icon: 'mdi-car-cog',
        to: route('admin.vehicles.index'),
        permissions: 'users all read'
      }
    ],
    //config:[{}]
  },
}
