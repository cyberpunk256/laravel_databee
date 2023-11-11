<template>
  <div ref="vp_wrap" class="vp_wrap">
  </div>
</template>

<script>
import { Viewer, VideoPanorama } from 'panolens';

export default {
  props: ['url', 'capture'],
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
      const self = this
      // Create a viewer for the panorama
      const viewer = new Viewer({
        container: this.$refs.vp_wrap,
      });

      // Create a VideoPanorama with your 360-degree video
      const panorama = new VideoPanorama(this.url, {
        autoplay: false, // Disable auto-play for custom control handling
      });

      // Add the VideoPanorama to the viewer
      viewer.add(panorama);
    },
  },
};
</script>
