<template>
  <v-app class="bg-grey-lighten-4">
    <!-- <three-video-player/> -->
    <div id="map" class="m_map"></div>
    <v-dialog
      v-model="modal"
      width="auto"
    >
      <v-card>
        <v-card-text>
          <video controls class="p_video">
            <source v-if="modal" :src="modalVideo" type="video/mp4">
          </video>
        </v-card-text>
        <v-card-actions>
          <v-btn color="primary" block @click="modal = false">Close Dialog</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-app>
</template>

<script>
import L from 'leaflet';
import 'leaflet-gpx';
import ThreeVideoPlayer from '@/Components/ThreeVideoPlayer.vue';

export default {
  components: { ThreeVideoPlayer },
  props: {
  },
  data() {
    return {
      map: null,
      zoom: 2,
      center: [47.41322, -1.219482],
      modal: false,
      modalVideo: null,
      gpxs: [
        {
          url: "VID_20230501_181734_00_009.gpx",
          video: "VID_20230501_181734_00_009.mp4"
        },
        {
          url: "https://mpetazzoni.github.io/leaflet-gpx/demo.gpx",
          video: "VID_20230501_181734_00_009.mp4"
        },
      ],
      view: [36.2048, 138.2529],
      gpxOptions: {
        async: true,
        marker_options: {
          startIconUrl: 'pin-icon-start.png',
          endIconUrl:   'pin-icon-end.png',
          shadowUrl:    'pin-shadow.png',
        },
        gpx_options: {
            joinTrackSegments: false
        }, 
      }
    };
  },
  mounted() {
    // const self = this
    // self.map = L.map('map').setView(this.view,6);
    // L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    //   attribution: 'Map data &copy; <a href="http://www.osm.org">OpenStreetMap</a>'
    // }).addTo(self.map)
    // for (let i = 0; i < self.gpxs.length; i++) {
    //   const item = self.gpxs[i];
    //   new L.GPX(item.url, this.gpxOptions)
    //     .on('loaded', self.onLoaded)
    //     .on('click', function() {
    //       self.onShowModal(item)
    //     });
    // }
  },
  methods: {
    onLoaded(e) {
      e.target.addTo(this.map);
    },
    onShowModal(item) {
      this.modal = true
      this.modalVideo = item.video
    }

    // onGpxLoaded(e) {
    //   var gpx = e.target;
    //   console.log('gpx.getBounds()', gpx);
    //   console.log('gpx.getBounds()', gpx.getBounds());
    //   this.map.leafletObject.fitBounds(gpx.getBounds());
    //   // control.addOverlay(gpx, gpx.get_name());

    //   // /*
    //   //   * Note: the code below relies on the fact that the demo GPX file is
    //   //   * an actual GPS track with timing and heartrate information.
    //   //   */
    //   _t('h3').textContent = gpx.get_name();
    //   _c('start').textContent = gpx.get_start_time().toDateString() + ', '
    //     + gpx.get_start_time().toLocaleTimeString();
    //   _c('distance').textContent = gpx.get_distance_imp().toFixed(2);
    //   _c('duration').textContent = gpx.get_duration_string(gpx.get_moving_time());
    //   _c('pace').textContent     = gpx.get_duration_string(gpx.get_moving_pace_imp(), true);
    //   _c('avghr').textContent    = gpx.get_average_hr();
    //   _c('elevation-gain').textContent = gpx.to_ft(gpx.get_elevation_gain()).toFixed(0);
    //   _c('elevation-loss').textContent = gpx.to_ft(gpx.get_elevation_loss()).toFixed(0);
    //   _c('elevation-net').textContent  = gpx.to_ft(gpx.get_elevation_gain()
    //     - gpx.get_elevation_loss()).toFixed(0);
    // }
  },
};
</script>