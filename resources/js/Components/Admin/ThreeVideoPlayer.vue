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
  watch: {
  },
  props: ['url', 'capture'],
  data() {
    return {
      panorama: null,
      isPlaying: false,
      progress: 0,
      disabled: false,
      step: 2
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
        controlBar: false
      });

      // Create a VideoPanorama with your 360-degree video
      const panorama = new VideoPanorama(this.url, {
        autoplay: false, // Disable auto-play for custom control handling
      });

      // Add the VideoPanorama to the viewer
      viewer.add(panorama);

      // Get a reference to the video element
      this.video = panorama.getVideoElement();
      this.video.addEventListener('timeupdate', function() {
          // ビデオの現在の再生時間と総時間を取得
          var currentTime = self.video.currentTime;
          var duration = self.video.duration;

          // プログレスバーの幅を更新
          self.progress = (currentTime / duration) * 100;
      });
    },
    onPause() {
      this.video.pause();
      this.isPlaying = false;
    },
    async onPlay() {
      try {
        await this.video.play();
        this.isPlaying = true
      } catch(e) {
        console.log(e)
      }
    },
    onRewind() {
      this.video.currentTime = this.video.currentTime < this.step ? 0 : this.video.currentTime - this.step;
    },
    onFast() {
      this.video.currentTime = this.video.duration - this.video.currentTime < this.step ? 
        this.video.duration : this.video.currentTime + this.step;
    },
    onSeekTo(event) {
      const boundingRect = event.currentTarget.getBoundingClientRect();
      const clickX = event.clientX - boundingRect.left;
      const fullWidth = boundingRect.width;
      const percent = (clickX / fullWidth) * 100;
      this.progress = percent;
      const time = parseInt((percent / 100) * this.video.duration);
      this.video.currentTime = time;
    },
  },
};
</script>
