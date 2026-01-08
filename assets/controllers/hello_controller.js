// assets/controllers/test_controller.js
import { Controller } from "@hotwired/stimulus";
export default class extends Controller {
  connect() {
    console.log("Stimulus OK");
  }
}
