<template>
  <div class="bot-filter">
    <div class="form-check form-switch">
      <input
        class="form-check-input"
        type="checkbox"
        id="excludeBotsSwitch"
        v-model="excludeBots"
        @change="handleChange"
      />
      <label class="form-check-label" for="excludeBotsSwitch">
        {{ excludeBots ? 'Bots Excluded' : 'Include Bots' }}
      </label>
    </div>
    <small class="text-muted d-block mt-1">
      {{ excludeBots ? 'Bot traffic is hidden from statistics' : 'Showing all traffic including bots' }}
    </small>
  </div>
</template>

<script>
import { ref } from 'vue';

export default {
  name: 'BotFilter',
  props: {
    modelValue: {
      type: Boolean,
      default: true,
    },
  },
  emits: ['update:modelValue'],
  setup(props, { emit }) {
    const excludeBots = ref(props.modelValue);

    const handleChange = () => {
      emit('update:modelValue', excludeBots.value);
    };

    return {
      excludeBots,
      handleChange,
    };
  },
};
</script>

<style scoped>
.bot-filter {
  padding: 15px;
  background: #f8f9fa;
  border-radius: 8px;
}

.form-check-input:checked {
  background-color: #667eea;
  border-color: #667eea;
}
</style>
