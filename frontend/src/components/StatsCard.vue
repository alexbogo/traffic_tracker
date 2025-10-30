<template>
  <div class="card stats-card h-100">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <p class="text-muted mb-1 small">{{ title }}</p>
          <h3 class="mb-0">
            <span v-if="loading" class="spinner-border spinner-border-sm"></span>
            <span v-else>{{ formattedValue }}</span>
          </h3>
        </div>
        <div v-if="icon" class="icon-wrapper">
          <i :class="icon"></i>
        </div>
      </div>
      <small v-if="subtitle" class="text-muted">{{ subtitle }}</small>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue';

export default {
  name: 'StatsCard',
  props: {
    title: {
      type: String,
      required: true,
    },
    value: {
      type: [Number, String],
      default: 0,
    },
    icon: {
      type: String,
      default: '',
    },
    subtitle: {
      type: String,
      default: '',
    },
    loading: {
      type: Boolean,
      default: false,
    },
  },
  setup(props) {
    const formattedValue = computed(() => {
      if (typeof props.value === 'number') {
        return props.value.toLocaleString();
      }
      return props.value;
    });

    return {
      formattedValue,
    };
  },
};
</script>

<style scoped>
.stats-card {
  border: none;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s, box-shadow 0.2s;
}

.stats-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.icon-wrapper {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 24px;
}
</style>
