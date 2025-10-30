<template>
  <div class="dashboard-view">
    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-gradient mb-4">
      <div class="container-fluid">
        <a class="navbar-brand" href="#">
          <strong>Traffic Tracker</strong>
        </a>
        <div class="d-flex align-items-center">
          <!-- User Dropdown -->
          <div class="dropdown me-2">
            <button
              class="btn btn-outline-light btn-sm dropdown-toggle"
              type="button"
              id="userDropdown"
              data-bs-toggle="dropdown"
              aria-expanded="false"
            >
              {{ username }}
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
              <li><h6 class="dropdown-header">Profile Information</h6></li>
              <li><hr class="dropdown-divider" /></li>
              <li class="px-3 py-2">
                <div class="profile-info">
                  <p class="mb-1"><strong>Username:</strong> {{ username }}</p>
                  <p class="mb-1"><strong>Email:</strong> {{ email }}</p>
                  <p class="mb-0"><strong>Role:</strong> {{ role }}</p>
                </div>
              </li>
              <li><hr class="dropdown-divider" /></li>
              <li>
                <a class="dropdown-item text-danger" href="#" @click.prevent="handleLogout">
                  Logout
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </nav>

    <div class="container-fluid">
      <!-- Control Panel -->
      <div class="row mb-4">
        <div class="col-lg-4 mb-3">
          <PageSelector
            v-model="selectedPageId"
            :pages="pages"
            :loading="pagesLoading"
            @page-selected="handlePageSelected"
          />
        </div>
        <div class="col-lg-5 mb-3">
          <DateRangePicker @update:dateRange="handleDateRangeChange" />
        </div>
        <div class="col-lg-3 mb-3">
          <BotFilter v-model="excludeBots" @update:modelValue="fetchStats" />
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="row mb-4">
        <div class="col-md-4 mb-3">
          <StatsCard
            title="Unique Visitors"
            :value="stats.unique_visitors || 0"
            icon="👥"
            :loading="statsLoading"
          />
        </div>
        <div class="col-md-4 mb-3">
          <StatsCard
            title="Total Visits"
            :value="stats.total_visits || 0"
            icon="📊"
            :loading="statsLoading"
          />
        </div>
        <div class="col-md-4 mb-3">
          <StatsCard
            title="Countries"
            :value="stats.countries_count || 0"
            icon="🌍"
            :loading="statsLoading"
          />
        </div>
      </div>

      <!-- Charts and Tables -->
      <div class="row">
        <div class="col-lg-8 mb-4">
          <VisitsChart :timeSeries="stats.time_series || []" :loading="statsLoading" />
        </div>
        <div class="col-lg-4 mb-4">
          <CountryStats :countries="stats.countries || []" :loading="statsLoading" />
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="!pagesLoading && pages.length === 0" class="text-center py-5">
        <div class="empty-state">
          <h3>No Pages Tracked Yet</h3>
          <p class="text-muted">Start tracking by visiting your demo pages or embedding the tracker on your website.</p>
          <a href="/demo/page1.html" target="_blank" class="btn btn-primary">Open Demo Page</a>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import authService from '@/services/auth';
import dashboardService from '@/services/dashboard';
import PageSelector from '@/components/PageSelector.vue';
import DateRangePicker from '@/components/DateRangePicker.vue';
import BotFilter from '@/components/BotFilter.vue';
import StatsCard from '@/components/StatsCard.vue';
import VisitsChart from '@/components/VisitsChart.vue';
import CountryStats from '@/components/CountryStats.vue';

export default {
  name: 'DashboardView',
  components: {
    PageSelector,
    DateRangePicker,
    BotFilter,
    StatsCard,
    VisitsChart,
    CountryStats,
  },
  setup() {
    const router = useRouter();
    const username = ref('');
    const email = ref('');
    const role = ref('');
    const pages = ref([]);
    const pagesLoading = ref(false);
    const selectedPageId = ref('');
    const dateRange = ref({ startDate: '', endDate: '' });
    const excludeBots = ref(true);
    const stats = ref({});
    const statsLoading = ref(false);

    // Fetch current user
    const fetchUser = async () => {
      const result = await authService.getCurrentUser();
      if (result.success) {
        username.value = result.data.username || 'User';
        email.value = result.data.email || 'N/A';
        // Get the highest role (prefer ROLE_ADMIN over ROLE_USER)
        const roles = result.data.roles || [];
        if (roles.includes('ROLE_ADMIN')) {
          role.value = 'Administrator';
        } else if (roles.includes('ROLE_USER')) {
          role.value = 'User';
        } else {
          role.value = 'Guest';
        }
      }
    };

    // Fetch all pages
    const fetchPages = async () => {
      pagesLoading.value = true;
      const result = await dashboardService.getPages();
      pagesLoading.value = false;

      if (result.success) {
        // Filter to show only demo pages (localhost:8080/demo/)
        pages.value = result.data.filter(page =>
          page.url.includes('localhost:8080/demo/')
        );
      }
    };

    // Fetch statistics
    const fetchStats = async () => {
      if (!selectedPageId.value) return;

      statsLoading.value = true;
      const result = await dashboardService.getPageStats(selectedPageId.value, {
        startDate: dateRange.value.startDate,
        endDate: dateRange.value.endDate,
        excludeBots: excludeBots.value,
      });
      statsLoading.value = false;

      if (result.success) {
        stats.value = result.data;
      }
    };

    // Handle page selection
    const handlePageSelected = (page) => {
      fetchStats();
    };

    // Handle date range change
    const handleDateRangeChange = (newRange) => {
      dateRange.value = newRange;
      fetchStats();
    };

    // Handle logout
    const handleLogout = async () => {
      await authService.logout();
      router.push('/login');
    };

    // Initialize
    onMounted(async () => {
      await fetchUser();
      await fetchPages();
    });

    return {
      username,
      email,
      role,
      pages,
      pagesLoading,
      selectedPageId,
      excludeBots,
      stats,
      statsLoading,
      handlePageSelected,
      handleDateRangeChange,
      handleLogout,
      fetchStats,
    };
  },
};
</script>

<style scoped>
.dashboard-view {
  min-height: 100vh;
  background: #f5f7fa;
}

.navbar {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.bg-gradient {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.empty-state {
  padding: 60px 20px;
  background: white;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.profile-info {
  font-size: 0.9em;
  color: #495057;
}

.profile-info p {
  margin-bottom: 0.5rem;
}

.dropdown-menu {
  min-width: 250px;
}

.dropdown-item:hover {
  background-color: #f8f9fa;
}
</style>
