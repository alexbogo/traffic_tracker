<template>
  <div class="card">
    <div class="card-header bg-white">
      <h5 class="mb-0">Visitors by Country</h5>
    </div>
    <div class="card-body">
      <!-- Loading State -->
      <div v-if="loading" class="text-center py-4">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else-if="!countries || countries.length === 0" class="text-center py-4 text-muted">
        <p>No country data available</p>
      </div>

      <!-- Country Table -->
      <div v-else class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th>Country</th>
              <th class="text-end">Visitors</th>
              <th class="text-end">Percentage</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="country in sortedCountries" :key="country.country_code">
              <td>
                <span class="country-flag me-2">{{ getCountryFlag(country.country_code) }}</span>
                <strong>{{ country.country_name || 'Unknown' }}</strong>
                <small class="text-muted ms-1">({{ country.country_code || 'N/A' }})</small>
              </td>
              <td class="text-end">{{ country.visitors.toLocaleString() }}</td>
              <td class="text-end">
                <span class="badge bg-primary">{{ calculatePercentage(country.visitors) }}%</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue';

export default {
  name: 'CountryStats',
  props: {
    countries: {
      type: Array,
      default: () => [],
    },
    loading: {
      type: Boolean,
      default: false,
    },
  },
  setup(props) {
    // Sort countries by visitor count (descending)
    const sortedCountries = computed(() => {
      return [...props.countries].sort((a, b) => b.visitors - a.visitors);
    });

    // Calculate total visitors
    const totalVisitors = computed(() => {
      return props.countries.reduce((sum, country) => sum + country.visitors, 0);
    });

    // Calculate percentage
    const calculatePercentage = (visitors) => {
      if (totalVisitors.value === 0) return 0;
      return ((visitors / totalVisitors.value) * 100).toFixed(1);
    };

    // Get country flag emoji
    const getCountryFlag = (countryCode) => {
      if (!countryCode || countryCode.length !== 2) return '🌍';
      const codePoints = countryCode
        .toUpperCase()
        .split('')
        .map((char) => 127397 + char.charCodeAt());
      return String.fromCodePoint(...codePoints);
    };

    return {
      sortedCountries,
      calculatePercentage,
      getCountryFlag,
    };
  },
};
</script>

<style scoped>
.card {
  border: none;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.card-header {
  border-bottom: 1px solid #e9ecef;
  padding: 15px 20px;
}

.country-flag {
  font-size: 1.2em;
}

.table th {
  border-top: none;
  font-weight: 600;
  color: #6c757d;
  font-size: 0.875rem;
  text-transform: uppercase;
}

.table td {
  vertical-align: middle;
}
</style>
