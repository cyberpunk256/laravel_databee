<template>
  <div ref="vp_wrap" class="vp_wrap">
  </div>
</template>

<script>
import { Viewer, ImagePanorama } from 'panolens';

export default {
  props: ['url'],
  data() {
    return {
      panorama: null,
      isPlaying: false,
      progress: 0,
      disabled: false
    };
  },
  mounted() {
    this.initVideoPlayer();
  },
  methods: {
    initVideoPlayer() {
      const panorama = new ImagePanorama(this.url);
      const viewer = new Viewer({
        container: this.$refs.vp_wrap,
      });
      viewer.add(panorama);
    },
  },
};
</script>
