<template>
  <div ref="vp_wrap" class="vp_wrap">
    <div class="vp_controls">
      <v-row justify="center">
        <v-col cols="auto">
          <v-btn icon="mdi-rewind" @click="onRewind"></v-btn>
        </v-col>
        <v-col cols="auto">
          <v-btn v-if="isPlaying" icon="mdi-stop" @click="onPause"></v-btn>
          <v-btn v-if="!isPlaying" icon="mdi-play" @click="onPlay"></v-btn>
        </v-col>
        <v-col cols="auto">
          <v-btn icon="mdi-fast-forward" @click="onFast"></v-btn>
        </v-col>
        <v-col cols="auto">
          <v-btn v-if="capture" icon="mdi-record" @click="onCapture"></v-btn>
        </v-col>
      </v-row>
      <div class="vp_progress mt-2">
        <v-progress-linear v-model="progress" @click="onSeekTo" :height="10"></v-progress-linear>
      </div>
    </div>
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
    };
  },
  mounted() {
    this.initVideoPlayer();
  },
  methods: {
    initVideoPlayer() {
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

      // Get a reference to the video element
      this.video = panorama.getVideoElement();
    },
    onTogglePlay() {
      if (this.isPlaying) {
        this.video.pause();
      } else {
        this.video.play();
      }
      this.isPlaying = !this.isPlaying;
    },
    seekTo(event) {
      const video = this.panorama.getVideoElement();
      const boundingRect = event.currentTarget.getBoundingClientRect();
      const clickX = event.clientX - boundingRect.left;
      const fullWidth = boundingRect.width;
      const percent = (clickX / fullWidth) * 100;
      this.progress = percent;
      console.log('video.duration', this.video.duration);
      const time = parseInt((percent / 100) * this.video.duration);
      this.video.currentTime += 10;
      console.log('time', video.currentTime);
    },
  },
};
</script>
