<template>
  <div class="min-vh-100 bg-light">
    <div v-if="!isAuthenticated" class="container pt-1 pb-5">
      <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-5">
          <div class="card shadow-sm">
            <div class="card-body">
              <div class="mb-3 text-center auth-landing-title">
                <img class="brand-logo mb-2" :src="mustLogoUrl" alt="MUST Logo" style="width: 300px; max-width: none;" />
                <div class="fw-bold brand-text-black" style="font-size: 1.7rem; line-height: 1.2;">Smart Academic Deadline</div>
              </div>
              <div class="btn-group w-100 mb-3">
                <button class="btn auth-switch-btn" :class="authMode==='login' ? 'btn-primary active' : 'btn-outline-primary'" @click="switchAuthMode('login')">
                  Login
                </button>
                <button class="btn auth-switch-btn" :class="authMode==='register' ? 'btn-primary active' : 'btn-outline-primary'" @click="switchAuthMode('register')">
                  Register
                </button>
              </div>

              <div v-if="authMode==='login'">
                <div class="mb-2">
                  <label class="form-label">Email</label>
                  <input class="form-control" v-model="loginForm.email" />
                </div>
                <div class="mb-2">
                  <label class="form-label">Password</label>
                  <input class="form-control" type="password" v-model="loginForm.password" />
                </div>
                <div class="mb-2">
                  <label class="form-label">Captcha</label>
                  <div class="d-flex gap-2 captcha-row">
                    <input class="form-control captcha-input" v-model="loginForm.captcha_answer" placeholder="Enter captcha" @keyup.enter="submitLogin" />
                    <img
                      v-if="loginCaptcha.image"
                      class="captcha-image border rounded"
                      :src="loginCaptcha.image"
                      alt="Captcha image"
                    />
                    <span v-else class="badge text-bg-secondary fs-6 px-3 py-2">-----</span>
                    <button class="btn btn-sm btn-outline-secondary flex-shrink-0" @click="fetchCaptcha('login')">
                      <i class="bi bi-arrow-clockwise"></i>
                    </button>
                  </div>
                </div>
                <button class="btn btn-primary w-100" :disabled="loginSubmitting" @click="submitLogin">
                  <template v-if="loginSubmitting">
                    <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Logging in...
                  </template>
                  <template v-else>
                    <i class="bi bi-box-arrow-in-right me-1"></i>Login
                  </template>
                </button>
              </div>

              <div v-else>
                <div class="mb-2">
                  <label class="form-label">Student Name</label>
                  <input class="form-control" v-model="registerForm.student_name" />
                </div>
                <div class="mb-2">
                  <label class="form-label">Student ID</label>
                  <input class="form-control" v-model="registerForm.student_id" />
                </div>
                <div class="mb-2">
                  <label class="form-label">Phone</label>
                  <input class="form-control" v-model="registerForm.phone" />
                </div>
                <div class="mb-2">
                  <label class="form-label">Email</label>
                  <input class="form-control" v-model="registerForm.email" />
                </div>
                <div class="mb-2">
                  <label class="form-label">Password</label>
                  <input class="form-control" type="password" v-model="registerForm.password" />
                </div>
                <div class="mb-2">
                  <label class="form-label">Re-Password</label>
                  <input class="form-control" type="password" v-model="registerForm.password_confirmation" />
                </div>
                <div class="mb-2">
                  <label class="form-label">Captcha</label>
                  <div class="d-flex gap-2 captcha-row">
                    <input class="form-control captcha-input" v-model="registerForm.captcha_answer" placeholder="Enter captcha" />
                    <img
                      v-if="registerCaptcha.image"
                      class="captcha-image border rounded"
                      :src="registerCaptcha.image"
                      alt="Captcha image"
                    />
                    <span v-else class="badge text-bg-secondary fs-6 px-3 py-2">-----</span>
                    <button class="btn btn-sm btn-outline-secondary flex-shrink-0" @click="fetchCaptcha('register')">
                      <i class="bi bi-arrow-clockwise"></i>
                    </button>
                  </div>
                </div>
                <button class="btn btn-primary w-100" @click="submitRegister">
                  <i class="bi bi-person-plus me-1"></i>Register
                </button>
              </div>

              <div class="small text-muted mt-3 text-center">
                Email must use domain <b>@must.edu.my</b> or subdomain <b>@*.must.edu.my</b>.
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <template v-else>
    <!-- NAVBAR -->
    <nav class="navbar navbar-dark bg-primary shadow-sm" style="min-height: 64px; padding-top: 0.4rem; padding-bottom: 0.4rem;">
      <div class="container-fluid py-0">
        <div class="navbar-brand fw-semibold mb-0 lh-sm">
          <div class="d-block fw-bold text-white" style="font-size: 1.7rem; line-height: 1.1;">Smart Academic Deadline</div>
        </div>

        <div class="d-flex gap-2">
          <button class="btn btn-outline-light btn-sm" @click="reloadAll">
            <i class="bi bi-arrow-clockwise me-1"></i> Refresh
          </button>
          <button class="btn btn-warning btn-sm fw-semibold" @click="openCourseCreate">
            <i class="bi bi-bookmark-plus me-1"></i> Subject
          </button>
          <button
            class="btn btn-warning btn-sm fw-semibold"
            :disabled="!selectedCourseId"
            @click="openTaskCreate"
          >
            <i class="bi bi-calendar-plus me-1"></i> Due Date
          </button>
          <div class="position-relative profile-menu-container">
            <button class="btn btn-light btn-sm fw-semibold" @click="profileMenuOpen = !profileMenuOpen">
              <i class="bi bi-person-circle me-1"></i> Profile ({{ currentUser?.name || "-" }})
            </button>
            <div v-if="profileMenuOpen" class="profile-menu card shadow-sm position-absolute end-0 mt-1">
              <div class="card-body p-2 d-grid gap-2">
                <button class="btn btn-sm btn-outline-primary text-start" @click="onClickProfileMode">
                  <i class="bi me-1" :class="isViewMode ? 'bi-eye' : 'bi-pencil-square'"></i>
                  {{ isViewMode ? "Mode View" : "Mode Edit" }}
                </button>
                <button class="btn btn-sm btn-outline-primary text-start" @click="onClickProfileConfig">
                  <i class="bi bi-gear me-1"></i> Config
                </button>
                <button class="btn btn-sm btn-outline-primary text-start" @click="onClickProfileChangePassword">
                  <i class="bi bi-key me-1"></i> Change Passwd
                </button>
                <button class="btn btn-sm btn-outline-danger text-start" @click="onClickProfileLogout">
                  <i class="bi bi-box-arrow-right me-1"></i> Logout
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <div class="container-fluid py-3">
      <div class="row g-3">
        <!-- LEFT: SUBJECTS -->
        <div class="col-12 col-lg-3">
          <div class="card shadow-sm">
            <!-- <div class="card-header bg-white d-flex justify-content-between align-items-center">
              <div>
                <div class="fw-bold text-primary">Subjects</div>
                <div class="small text-muted">Add Subject Code & Name first</div>
              </div>
              <button class="btn btn-primary btn-sm" @click="openCourseCreate">
                <i class="bi bi-plus-lg"></i>
              </button>
            </div> -->

            <div class="card-body">
              <div class="list-group">
                <div
                  v-for="c in filteredCourses"
                  :key="c.id"
                  class="list-group-item list-group-item-action subject-list-item"
                  :class="c.id === selectedCourseId ? 'active' : ''"
                  @click="selectCourse(c.id)"
                >
                  <div class="mb-2">
                    <div class="fw-semibold d-flex align-items-center justify-content-between">
                      <span>{{ c.code }}</span>
                      <span v-if="dueSoonCountByCourse.get(Number(c.id)) > 0" class="due-soon-square" aria-hidden="true">
                        <i class="bi bi-circle-fill text-danger"></i>
                        <span class="due-soon-square-count">
                          {{ formatBadgeCount(dueSoonCountByCourse.get(Number(c.id))) }}
                        </span>
                      </span>
                    </div>
                    <div class="small opacity-75">{{ (c.name || "").toUpperCase() }}</div>
                  </div>

                  <!-- ICON ACTIONS -->
                  <div v-if="!isViewMode" class="btn-group" @click.stop>
                    <button
                      class="btn btn-sm"
                      :class="c.id === selectedCourseId ? 'btn-light' : 'btn-outline-primary'"
                      title="Edit Subject"
                      @click="openCourseEdit(c)"
                    >
                      <i class="bi bi-pencil"></i>
                    </button>
                    <button
                      class="btn btn-sm"
                      :class="c.id === selectedCourseId ? 'btn-light' : 'btn-outline-danger'"
                      title="Delete Subject"
                      @click="removeCourse(c)"
                    >
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                </div>

                <div v-if="filteredCourses.length === 0" class="text-center text-muted py-3">
                  No subjects yet. Click <b>Add Subject</b>.
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- RIGHT: TASKS -->
        <div class="col-12 col-lg-9">
          <div class="row g-3">
            <!-- MAIN RIGHT -->
            <div class="col-12" :class="showRightCards ? 'col-lg-9' : 'col-lg-12'">
              <div v-if="viewCards.dueDates" class="card shadow-sm mb-3">
                <div class="card-body">
                  <div class="d-flex justify-content-between flex-wrap gap-2 align-items-start">
                    <div>
                      <h4 class="mb-1 due-dates-title">Due Dates</h4>
                      <div class="text-muted small">
                        <span class="fw-semibold" v-if="selectedCourse">
                          {{ selectedCourse.code }} - {{ (selectedCourse.name || "").toUpperCase() }}
                        </span>
                        <span v-else class="text-danger fw-semibold">None (select/add subject first)</span>
                      </div>
                    </div>

                    <div class="d-flex gap-2">
                      <div class="btn-group">
                        <button class="btn btn-sm" :class="tab==='all'?'btn-primary':'btn-outline-primary'" @click="tab='all'">All</button>
                        <button class="btn btn-sm" :class="tab==='pending'?'btn-primary':'btn-outline-primary'" @click="tab='pending'">Pending</button>
                        <button class="btn btn-sm" :class="tab==='done'?'btn-primary':'btn-outline-primary'" @click="tab='done'">Done</button>
                      </div>

                      <!-- <button class="btn btn-sm btn-warning fw-semibold" :disabled="!selectedCourseId" @click="openTaskCreate">
                        <i class="bi bi-plus-lg me-1"></i> Add Due
                      </button> -->
                    </div>
                  </div>

                  <!-- stats -->
                  <div class="row g-2 mt-2">
                    <div class="col-12 col-md-4">
                      <div class="border rounded p-2 bg-white">
                        <div class="small text-muted">Now</div>
                        <div class="fw-bold">{{ nowDateText }}</div>
                        <div class="small text-muted">{{ nowTimeText }}</div>
                      </div>
                    </div>
                    <div class="col-6 col-md-4">
                      <div class="border rounded p-2 bg-white">
                        <div class="small text-muted">Overdue</div>
                        <div class="fw-bold text-danger">{{ overdueCount }}</div>
                      </div>
                    </div>
                    <div class="col-6 col-md-4">
                      <div class="border rounded p-2 bg-white">
                        <div class="small text-muted">Due in 7 days</div>
                        <div class="fw-bold text-warning">{{ dueSoonCount }}</div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- tasks table -->
              <div class="card shadow-sm">
                <div class="table-responsive">
                  <table class="table table-hover align-middle mb-0">
                    <thead class="due-table-head">
                      <tr>
                        <th>Title</th>
                        <th>Due</th>
                        <th>Left</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                      </tr>
                    </thead>

                    <tbody>
                      <tr v-for="t in filteredTasks" :key="t.id" :class="{ 'due-soon-row': isDueSoonTask(t) }">
                        <td class="fw-semibold">
                          <div>{{ t.title }}</div>
                          <div class="d-inline-flex align-items-center gap-2 mt-1">
                            <span class="badge text-bg-secondary">{{ t.category }}</span>
                            <button
                              v-if="hasNote(t)"
                              class="btn btn-sm btn-outline-dark py-0 px-2"
                              title="Open Note"
                              @click="openNoteView(t)"
                            >
                              <i class="bi bi-journal-text"></i>
                            </button>
                          </div>
                        </td>
                        <!-- <td><span class="badge text-bg-secondary">{{ t.category }}</span></td> -->
                        <td>
                          <div>{{ fmtDateDate(t.due_at) }}</div>
                          <div class="small text-muted">{{ fmtDateTime(t.due_at) }}</div>
                        </td>
                        <td><span class="fw-semibold" :class="leftClassBs(t)">{{ leftText(t) }}</span></td>
                        <td>
                          <span class="badge" :class="t.status==='DONE' ? 'text-bg-success' : 'text-bg-warning'">
                            {{ t.status }}
                          </span>
                        </td>

                        <!-- ICON ACTIONS -->
                        <td class="text-end">
                          <div class="btn-group">
                            <!-- DONE / UNDO -->
                            <button
                              class="btn btn-sm"
                              :class="t.status==='DONE' ? 'btn-outline-secondary' : 'btn-outline-success'"
                              :title="t.status==='DONE' ? 'Undo' : 'Mark as Done'"
                              @click="openToggleDoneConfirm(t)"
                            >
                              <i :class="t.status==='DONE' ? 'bi bi-arrow-counterclockwise' : 'bi bi-check2'"></i>
                            </button>

                            <!-- EDIT -->
                            <button
                              v-if="!isViewMode"
                              class="btn btn-sm btn-outline-primary"
                              title="Edit Due Date"
                              @click="openTaskEdit(t)"
                            >
                              <i class="bi bi-pencil"></i>
                            </button>

                            <!-- DELETE -->
                            <button
                              v-if="!isViewMode"
                              class="btn btn-sm btn-outline-danger"
                              title="Delete Due Date"
                              @click="removeTask(t)"
                            >
                              <i class="bi bi-trash"></i>
                            </button>
                          </div>
                        </td>
                      </tr>

                      <tr v-if="filteredTasks.length === 0">
                        <td colspan="6" class="text-center text-muted py-4">
                          <span v-if="!selectedCourseId">Select a subject first.</span>
                          <span v-else>No due dates yet. Click “Add Due”.</span>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <!-- SIDE CARD (SEBELAH KANAN) -->
            <div v-if="showRightCards" class="col-12 col-lg-3">
              <div
                v-if="viewCards.upcoming"
                class="card shadow-sm mb-3"
                :class="upcomingTasks.length > 0 ? 'upcoming-alert' : ''"
              >
                <div class="card-header bg-white">
                  <div class="fw-bold text-primary">Upcoming</div>
                  <div class="small text-muted">Next 7 days</div>
                </div>
                <div class="card-body">
                  <div v-if="upcomingTasks.length === 0" class="text-muted small">
                    No upcoming due dates.
                  </div>

                  <div v-else class="list-group list-group-flush">
                    <div v-for="t in upcomingTasks" :key="t.id" class="list-group-item px-0">
                      <div class="d-flex justify-content-between">
                        <div class="fw-semibold small">{{ t.title }}</div>
                        <span class="badge text-bg-secondary">{{ t.category }}</span>
                        <!-- <span class="badge text-bg-secondary upcoming-badge">{{ t.category }}</span> -->
                      </div>
                      <div class="small text-muted">
                        <div>{{ fmtDateDate(t.due_at) }}</div>
                        <div>{{ fmtDateTime(t.due_at) }}</div>
                      </div>
                      <div class="small upcoming-left-text" :class="leftClassBs(t)">{{ leftText(t) }}</div>
                    </div>
                  </div>
                </div>
              </div>

              <div v-if="viewCards.quickSummary" class="card shadow-sm">
                <div class="card-header bg-white">
                  <div class="fw-bold text-primary">Quick Summary</div>
                </div>
                <div class="card-body">
                  <div class="d-flex justify-content-between">
                    <span class="text-muted">Total</span>
                    <span class="fw-bold">{{ tasks.length }}</span>
                  </div>
                  <div class="d-flex justify-content-between mt-1">
                    <span class="text-muted">Pending</span>
                    <span class="fw-bold">{{ tasks.filter(x => x.status==='PENDING').length }}</span>
                  </div>
                  <div class="d-flex justify-content-between mt-1">
                    <span class="text-muted">Done</span>
                    <span class="fw-bold">{{ tasks.filter(x => x.status==='DONE').length }}</span>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>

      </div>
    </div>

    <!-- CONFIG MODAL -->
    <div v-if="configModalOpen" class="modal-backdrop fade show"></div>
    <div v-if="configModalOpen" class="modal d-block" tabindex="-1" @click.self="configModalOpen = false">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-info text-white">
            <h5 class="modal-title">View Configuration</h5>
            <button type="button" class="btn-close btn-close-white" @click="configModalOpen = false"></button>
          </div>

          <div class="modal-body">
            <div class="form-check form-switch mb-3">
              <input id="cfgDueDates" class="form-check-input" type="checkbox" v-model="viewCards.dueDates" />
              <label class="form-check-label" for="cfgDueDates">View card Due Dates</label>
            </div>
            <div class="form-check form-switch mb-3">
              <input id="cfgUpcoming" class="form-check-input" type="checkbox" v-model="viewCards.upcoming" />
              <label class="form-check-label" for="cfgUpcoming">View card Upcoming</label>
            </div>
            <div class="form-check form-switch">
              <input id="cfgQuickSummary" class="form-check-input" type="checkbox" v-model="viewCards.quickSummary" />
              <label class="form-check-label" for="cfgQuickSummary">View card Quick Summary</label>
            </div>
          </div>

          <div class="modal-footer">
            <button class="btn btn-outline-secondary" @click="configModalOpen = false">Close</button>
          </div>
        </div>
      </div>
    </div>

    <!-- SUBJECT MODAL -->
    <div v-if="courseModalOpen" class="modal-backdrop fade show"></div>
    <div v-if="courseModalOpen" class="modal d-block" tabindex="-1" @click.self="courseModalOpen = false">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">{{ courseForm.id ? "Edit Subject" : "Add Subject" }}</h5>
            <button type="button" class="btn-close btn-close-white" @click="courseModalOpen = false"></button>
          </div>

          <div class="modal-body">
            <div class="mb-2">
              <label class="form-label">Subject Code</label>
              <input class="form-control" v-model="courseForm.code" placeholder="e.g. BIT3134" />
            </div>
            <div class="mb-2">
              <label class="form-label">Subject Name</label>
              <input class="form-control" v-model="courseForm.name" placeholder="e.g. System Analysis and Design" />
            </div>
          </div>

          <div class="modal-footer">
            <button class="btn btn-outline-secondary" @click="courseModalOpen = false">Cancel</button>
            <button class="btn btn-primary" @click="saveCourse">Save</button>
          </div>
        </div>
      </div>
    </div>

    <!-- DELETE SUBJECT CONFIRM MODAL -->
    <div v-if="deleteCourseModalOpen" class="modal-backdrop fade show"></div>
    <div v-if="deleteCourseModalOpen" class="modal d-block" tabindex="-1" @click.self="closeDeleteCourseConfirm">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title">Delete Subject</h5>
            <button type="button" class="btn-close btn-close-white" @click="closeDeleteCourseConfirm"></button>
          </div>

          <div class="modal-body">
            <p class="mb-1">Delete subject {{ courseToDelete?.code || "-" }}?</p>
            <div class="small text-muted">This will delete its tasks too.</div>
          </div>

          <div class="modal-footer">
            <button class="btn btn-outline-secondary" @click="closeDeleteCourseConfirm">Cancel</button>
            <button class="btn btn-danger" @click="confirmRemoveCourse">Delete</button>
          </div>
        </div>
      </div>
    </div>

    <!-- TASK MODAL -->
    <div v-if="taskModalOpen" class="modal-backdrop fade show"></div>
    <div v-if="taskModalOpen" class="modal d-block" tabindex="-1" @click.self="taskModalOpen = false">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-warning">
            <h5 class="modal-title">{{ taskForm.id ? "Edit Due Date" : "Add Due Date" }}</h5>
            <button type="button" class="btn-close" @click="taskModalOpen = false"></button>
          </div>

          <div class="modal-body">
            <div class="mb-2">
              <label class="form-label">Subject</label>
              <input
                class="form-control"
                :value="selectedCourse ? selectedCourse.code + ' - ' + (selectedCourse.name || '').toUpperCase() : ''"
                disabled
              />
            </div>

            <div class="mb-2">
              <label class="form-label">Title</label>
              <input class="form-control" v-model="taskForm.title" placeholder="e.g. Assignment 1 - 40%" />
            </div>

            <div class="mb-2">
              <label class="form-label">Category</label>
              <select class="form-select" v-model="taskForm.category">
                <option>ASSIGNMENT</option>
                <option>ACTIVITY</option>
                <option>TUTORIAL</option>
                <option>MIDTERM</option>
                <option>EXAM</option>
                <option>PRESENTATION</option>
                <option>PROJECT</option>
                <option>OTHERS</option>
              </select>
            </div>

            <div class="mb-2">
              <label class="form-label">Due At</label>
              <input class="form-control" type="datetime-local" v-model="taskForm.due_at_local" />
            </div>

            <div class="mb-2">
              <label class="form-label">Notes</label>
              <textarea class="form-control" rows="3" v-model="taskForm.notes"></textarea>
            </div>

            <div class="mb-2" v-if="taskForm.id">
              <label class="form-label">Status</label>
              <select class="form-select" v-model="taskForm.status">
                <option>PENDING</option>
                <option>DONE</option>
              </select>
            </div>
          </div>

          <div class="modal-footer">
            <button class="btn btn-outline-secondary" @click="taskModalOpen = false">Cancel</button>
            <button class="btn btn-warning fw-semibold" :disabled="!selectedCourseId" @click="saveTask">
              Save
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- DELETE TASK CONFIRM MODAL -->
    <div v-if="deleteTaskModalOpen" class="modal-backdrop fade show"></div>
    <div v-if="deleteTaskModalOpen" class="modal d-block" tabindex="-1" @click.self="closeDeleteTaskConfirm">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title">Delete Due Date</h5>
            <button type="button" class="btn-close btn-close-white" @click="closeDeleteTaskConfirm"></button>
          </div>

          <div class="modal-body">
            <p class="mb-1">Delete this due date?</p>
            <div class="small text-muted">{{ taskToDelete?.title || "-" }}</div>
          </div>

          <div class="modal-footer">
            <button class="btn btn-outline-secondary" @click="closeDeleteTaskConfirm">Cancel</button>
            <button class="btn btn-danger" @click="confirmRemoveTask">Delete</button>
          </div>
        </div>
      </div>
    </div>

    <!-- CHANGE PASSWORD MODAL -->
    <div v-if="changePasswordModalOpen" class="modal-backdrop fade show"></div>
    <div v-if="changePasswordModalOpen" class="modal d-block" tabindex="-1" @click.self="closeChangePassword">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-info text-white">
            <h5 class="modal-title">Change Password</h5>
            <button type="button" class="btn-close btn-close-white" @click="closeChangePassword"></button>
          </div>

          <div class="modal-body">
            <div class="mb-2">
              <label class="form-label">Current Password</label>
              <input class="form-control" type="password" v-model="changePasswordForm.current_password" />
            </div>
            <div class="mb-2">
              <label class="form-label">New Password</label>
              <input class="form-control" type="password" v-model="changePasswordForm.password" />
            </div>
            <div class="mb-0">
              <label class="form-label">Re-Password</label>
              <input class="form-control" type="password" v-model="changePasswordForm.password_confirmation" />
            </div>
          </div>

          <div class="modal-footer">
            <button class="btn btn-outline-secondary" @click="closeChangePassword">Cancel</button>
            <button class="btn btn-info text-white" @click="submitChangePassword">Save</button>
          </div>
        </div>
      </div>
    </div>

    <!-- TOGGLE TASK STATUS CONFIRM MODAL -->
    <div v-if="toggleDoneModalOpen" class="modal-backdrop fade show"></div>
    <div v-if="toggleDoneModalOpen" class="modal d-block" tabindex="-1" @click.self="closeToggleDoneConfirm">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-warning">
            <h5 class="modal-title">{{ toggleDoneNextStatus === "DONE" ? "Mark as Done" : "Undo Due Date" }}</h5>
            <button type="button" class="btn-close" @click="closeToggleDoneConfirm"></button>
          </div>

          <div class="modal-body">
            <p class="mb-1">
              {{ toggleDoneNextStatus === "DONE" ? "Mark this due date as DONE?" : "Change this due date back to PENDING?" }}
            </p>
            <div class="small text-muted">{{ taskToToggleDone?.title || "-" }}</div>
          </div>

          <div class="modal-footer">
            <button class="btn btn-outline-secondary" @click="closeToggleDoneConfirm">Cancel</button>
            <button class="btn btn-warning fw-semibold" @click="confirmToggleDone">
              {{ toggleDoneNextStatus === "DONE" ? "Mark Done" : "Undo" }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- NOTE VIEW MODAL -->
    <div v-if="noteModalOpen" class="modal-backdrop fade show"></div>
    <div v-if="noteModalOpen" class="modal d-block" tabindex="-1" @click.self="closeNoteView">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-secondary text-white">
            <h5 class="modal-title">Note</h5>
            <button type="button" class="btn-close btn-close-white" @click="closeNoteView"></button>
          </div>

          <div class="modal-body">
            <div class="mb-2">
              <label class="form-label">Title</label>
              <input class="form-control" :value="noteViewTask?.title || '-'" disabled />
            </div>
            <div class="mb-0">
              <label class="form-label">Note</label>
              <textarea class="form-control" rows="5" :value="noteViewTask?.notes || ''" disabled></textarea>
            </div>
          </div>

          <div class="modal-footer">
            <button class="btn btn-outline-secondary" @click="closeNoteView">Close</button>
          </div>
        </div>
      </div>
    </div>
    </template>

    <!-- ALERT MODAL -->
    <div v-if="alertModalOpen" class="modal-backdrop fade show"></div>
    <div v-if="alertModalOpen" class="modal d-block" tabindex="-1" @click.self="closeAlert">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title">{{ alertModalTitle }}</h5>
            <button type="button" class="btn-close btn-close-white" @click="closeAlert"></button>
          </div>
          <div class="modal-body">
            <p class="mb-0">{{ alertModalMessage }}</p>
          </div>
          <div class="modal-footer">
            <button class="btn btn-outline-secondary" @click="closeAlert">Close</button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from "vue";
import { api } from "./lib/api";

const mustLogoUrl = "/images/MUST-LOGO.png";

/* =======================
   STATE
======================= */
const tab = ref("all");
const isViewMode = ref(true);
const configModalOpen = ref(false);
const viewCards = ref({
  dueDates: true,
  upcoming: true,
  quickSummary: true,
});
const VIEW_MODE_COOKIE = "sad_view_mode";
const VIEW_CARDS_COOKIE = "sad_view_cards";
const SELECTED_COURSE_COOKIE = "sad_selected_course_id";
const AUTH_TOKEN_KEY = "sad_auth_token";

const authMode = ref("login");
const authToken = ref(localStorage.getItem(AUTH_TOKEN_KEY) || "");
const currentUser = ref(null);
const loginCaptcha = ref({ id: "", image: "" });
const registerCaptcha = ref({ id: "", image: "" });
const loginForm = ref({
  email: "",
  password: "",
  captcha_answer: "",
});
const registerForm = ref({
  student_name: "",
  student_id: "",
  phone: "",
  email: "",
  password: "",
  password_confirmation: "",
  captcha_answer: "",
});
const loginSubmitting = ref(false);
const changePasswordModalOpen = ref(false);
const profileMenuOpen = ref(false);
const changePasswordForm = ref({
  current_password: "",
  password: "",
  password_confirmation: "",
});
const isAuthenticated = computed(() => !!authToken.value && !!currentUser.value);
const alertModalOpen = ref(false);
const alertModalTitle = ref("Alert");
const alertModalMessage = ref("");

const courses = ref([]);
const courseSearch = ref("");
const selectedCourseId = ref(null);

const allTasks = ref([]);
const tasks = ref([]);
const taskSearch = ref("");

/* NOW (manual refresh only) */
const now = ref(new Date());
const nowDateText = computed(() => fmtDateDate(now.value));
const nowTimeText = computed(() => fmtDateTime(now.value));

/* =======================
   COMPUTED
======================= */
const selectedCourse = computed(() => courses.value.find(c => c.id === selectedCourseId.value) || null);
const coursePriorityById = computed(() => {
  // 0 = has pending (top), 1 = no task/unknown (middle), 2 = done/completed only (bottom)
  const priority = new Map();
  for (const t of allTasks.value) {
    const courseId = Number(t.course_id);
    if (!Number.isFinite(courseId)) continue;
    const status = String(t.status || "").trim().toUpperCase();

    if (status === "PENDING") {
      priority.set(courseId, 0);
      continue;
    }

    if (!priority.has(courseId)) {
      priority.set(courseId, 2);
    }
  }
  return priority;
});

const filteredCourses = computed(() => {
  const q = courseSearch.value.trim().toLowerCase();
  const arr = !q
    ? [...courses.value]
    : courses.value.filter(c =>
    (c.code || "").toLowerCase().includes(q) || (c.name || "").toLowerCase().includes(q)
  );

  return arr.sort((a, b) => {
    const aPriority = coursePriorityById.value.get(Number(a.id)) ?? 1;
    const bPriority = coursePriorityById.value.get(Number(b.id)) ?? 1;
    if (aPriority !== bPriority) return aPriority - bPriority;
    return (a.code || "").localeCompare(b.code || "");
  });
});

const filteredTasks = computed(() => {
  let arr = [...tasks.value];
  if (tab.value === "pending") arr = arr.filter(t => t.status === "PENDING");
  if (tab.value === "done") arr = arr.filter(t => t.status === "DONE");

  const q = taskSearch.value.trim().toLowerCase();
  if (q) arr = arr.filter(t => (t.title || "").toLowerCase().includes(q));

  return arr.sort((a, b) => {
    const aStatus = String(a.status || "").toUpperCase();
    const bStatus = String(b.status || "").toUpperCase();
    const aPriority = aStatus === "PENDING" ? 0 : 1;
    const bPriority = bStatus === "PENDING" ? 0 : 1;
    if (aPriority !== bPriority) return aPriority - bPriority;
    return new Date(a.due_at) - new Date(b.due_at);
  });
});

const upcomingTasks = computed(() => {
  const start = now.value;
  const end = new Date(start.getTime() + 7 * 24 * 3600 * 1000);

  return tasks.value
    .filter(t => t.status !== "DONE")
    .filter(t => new Date(t.due_at) >= start && new Date(t.due_at) <= end)
    .sort((a, b) => new Date(a.due_at) - new Date(b.due_at))
    .slice(0, 8);
});

const overdueCount = computed(() =>
  tasks.value.filter(t => t.status !== "DONE" && new Date(t.due_at) < now.value).length
);

const dueSoonCount = computed(() => {
  const week = new Date(now.value.getTime() + 7 * 24 * 3600 * 1000);
  return tasks.value.filter(t => t.status !== "DONE" && new Date(t.due_at) >= now.value && new Date(t.due_at) <= week).length;
});
const dueSoonCountByCourse = computed(() => {
  const week = new Date(now.value.getTime() + 7 * 24 * 3600 * 1000);
  const result = new Map();
  for (const t of allTasks.value) {
    if (String(t.status || "").toUpperCase() === "DONE") continue;
    const dueAt = new Date(t.due_at);
    if (Number.isNaN(dueAt.getTime())) continue;
    if (dueAt < now.value || dueAt > week) continue;
    const courseId = Number(t.course_id);
    if (!Number.isFinite(courseId)) continue;
    result.set(courseId, (result.get(courseId) || 0) + 1);
  }
  return result;
});

const showRightCards = computed(() => viewCards.value.upcoming || viewCards.value.quickSummary);

function getApiErrorMessage(e) {
  const data = e?.response?.data;
  if (typeof data === "string") return data;
  if (data?.errors) {
    const emailError = Array.isArray(data.errors.email) ? data.errors.email[0] : null;
    if (emailError) return emailError;
    const firstError = Object.values(data.errors)[0]?.[0];
    if (firstError) return firstError;
  }
  if (data?.message) return data.message;
  return e?.message || "Unknown error";
}

function showAlert(message, title = "Alert") {
  alertModalTitle.value = title;
  alertModalMessage.value = String(message || "");
  alertModalOpen.value = true;
}

function closeAlert() {
  alertModalOpen.value = false;
  alertModalMessage.value = "";
}

function applyAuthToken(token) {
  if (token) {
    api.defaults.headers.common.Authorization = `Bearer ${token}`;
  } else {
    delete api.defaults.headers.common.Authorization;
  }
}

function setAuthToken(token) {
  authToken.value = token || "";
  if (authToken.value) {
    localStorage.setItem(AUTH_TOKEN_KEY, authToken.value);
  } else {
    localStorage.removeItem(AUTH_TOKEN_KEY);
  }
  applyAuthToken(authToken.value);
}

function switchAuthMode(mode) {
  authMode.value = mode;
}

async function fetchCaptcha(target) {
  const res = await api.post("/api/auth/captcha");
  if (target === "register") {
    registerCaptcha.value = { id: res.data.captcha_id, image: res.data.captcha_image || "" };
    registerForm.value.captcha_answer = "";
    return;
  }
  loginCaptcha.value = { id: res.data.captcha_id, image: res.data.captcha_image || "" };
  loginForm.value.captcha_answer = "";
}

async function submitLogin() {
  if (loginSubmitting.value) return;
  loginSubmitting.value = true;
  try {
    const res = await api.post("/api/auth/login", {
      email: loginForm.value.email,
      password: loginForm.value.password,
      captcha_id: loginCaptcha.value.id,
      captcha_answer: loginForm.value.captcha_answer,
    });
    await new Promise(resolve => setTimeout(resolve, 1800));
    setAuthToken(res.data.token);
    currentUser.value = res.data.user;
    await reloadAll();
  } catch (e) {
    showAlert("Login failed: " + getApiErrorMessage(e), "Login Failed");
    await fetchCaptcha("login");
  } finally {
    loginSubmitting.value = false;
  }
}

async function submitRegister() {
  try {
    const res = await api.post("/api/auth/register", {
      student_name: registerForm.value.student_name,
      student_id: registerForm.value.student_id,
      phone: registerForm.value.phone,
      email: registerForm.value.email,
      password: registerForm.value.password,
      password_confirmation: registerForm.value.password_confirmation,
      captcha_id: registerCaptcha.value.id,
      captcha_answer: registerForm.value.captcha_answer,
    });
    setAuthToken(res.data.token);
    currentUser.value = res.data.user;
    await reloadAll();
  } catch (e) {
    showAlert("Registration failed: " + getApiErrorMessage(e), "Registration Failed");
    await fetchCaptcha("register");
  }
}

async function fetchMe() {
  const res = await api.get("/api/auth/me");
  currentUser.value = res.data;
}

async function initAuth() {
  if (authToken.value) {
    applyAuthToken(authToken.value);
    try {
      await fetchMe();
      await reloadAll();
      return;
    } catch {
      setAuthToken("");
      currentUser.value = null;
    }
  }
  await Promise.all([fetchCaptcha("login"), fetchCaptcha("register")]);
}

async function logout() {
  try {
    await api.post("/api/auth/logout");
  } catch {
    // Ignore logout request failure and clear client state anyway.
  }
  setAuthToken("");
  currentUser.value = null;
  courses.value = [];
  tasks.value = [];
  allTasks.value = [];
  selectedCourseId.value = null;
  await Promise.all([fetchCaptcha("login"), fetchCaptcha("register")]);
}

function closeChangePassword() {
  changePasswordModalOpen.value = false;
  changePasswordForm.value = {
    current_password: "",
    password: "",
    password_confirmation: "",
  };
}

async function submitChangePassword() {
  try {
    await api.post("/api/auth/change-password", {
      current_password: changePasswordForm.value.current_password,
      password: changePasswordForm.value.password,
      password_confirmation: changePasswordForm.value.password_confirmation,
    });
    showAlert("Password changed. Please login again.", "Success");
    closeChangePassword();
    await logout();
  } catch (e) {
    showAlert("Change password failed: " + getApiErrorMessage(e), "Change Password Failed");
  }
}

function onClickProfileMode() {
  toggleMode();
  profileMenuOpen.value = false;
}

function onClickProfileConfig() {
  configModalOpen.value = true;
  profileMenuOpen.value = false;
}

function onClickProfileChangePassword() {
  changePasswordModalOpen.value = true;
  profileMenuOpen.value = false;
}

async function onClickProfileLogout() {
  profileMenuOpen.value = false;
  await logout();
}

function handleGlobalClick(event) {
  const target = event.target;
  if (!(target instanceof Element)) return;
  if (!target.closest(".profile-menu-container")) {
    profileMenuOpen.value = false;
  }
}

function toggleMode() {
  isViewMode.value = !isViewMode.value;
}

function setCookie(name, value, days = 365) {
  const expires = new Date(Date.now() + days * 24 * 60 * 60 * 1000).toUTCString();
  document.cookie = `${name}=${encodeURIComponent(value)}; expires=${expires}; path=/; SameSite=Lax`;
}

function getCookie(name) {
  const target = `${name}=`;
  const parts = document.cookie.split(";").map(v => v.trim());
  const found = parts.find(v => v.startsWith(target));
  return found ? decodeURIComponent(found.slice(target.length)) : null;
}

function clearCookie(name) {
  document.cookie = `${name}=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/; SameSite=Lax`;
}

function loadUiPrefsFromCookies() {
  const mode = getCookie(VIEW_MODE_COOKIE);
  if (mode === "true" || mode === "false") {
    isViewMode.value = mode === "true";
  } else {
    // First load fallback: default to view mode and persist it.
    isViewMode.value = true;
    setCookie(VIEW_MODE_COOKIE, "true");
  }

  const cardsRaw = getCookie(VIEW_CARDS_COOKIE);
  if (!cardsRaw) return;

  try {
    const parsed = JSON.parse(cardsRaw);
    viewCards.value = {
      dueDates: parsed?.dueDates !== false,
      upcoming: parsed?.upcoming !== false,
      quickSummary: parsed?.quickSummary !== false,
    };
  } catch {
    // Ignore invalid cookie format and keep defaults.
  }
}

/* =======================
   API LOADERS
======================= */
async function loadCourses() {
  const res = await api.get("/api/courses", { params: courseSearch.value ? { q: courseSearch.value } : {} });
  courses.value = res.data;

  const selectedFromCookie = Number(getCookie(SELECTED_COURSE_COOKIE));
  const hasSelectedCourse = courses.value.some(c => c.id === selectedCourseId.value);
  const hasCookieCourse = Number.isFinite(selectedFromCookie) && courses.value.some(c => c.id === selectedFromCookie);

  if (!hasSelectedCourse) {
    if (hasCookieCourse) {
      selectedCourseId.value = selectedFromCookie;
    } else if (courses.value.length > 0) {
      selectedCourseId.value = courses.value[0].id;
    } else {
      selectedCourseId.value = null;
    }
  }
}

async function loadTasks() {
  if (!selectedCourseId.value) {
    tasks.value = [];
    return;
  }
  const res = await api.get("/api/tasks", { params: { course_id: selectedCourseId.value } });
  tasks.value = res.data;
}

async function loadAllTasksForCourseOrder() {
  const res = await api.get("/api/tasks");
  allTasks.value = res.data;
}

async function reloadAll() {
  now.value = new Date();
  await loadCourses();
  await Promise.all([loadTasks(), loadAllTasksForCourseOrder()]);
}

/* =======================
   SUBJECT CRUD
======================= */
const courseModalOpen = ref(false);
const courseForm = ref({ id: null, code: "", name: "" });
const deleteCourseModalOpen = ref(false);
const courseToDelete = ref(null);

function openCourseCreate() {
  courseForm.value = { id: null, code: "", name: "" };
  courseModalOpen.value = true;
}

function openCourseEdit(c) {
  courseForm.value = { id: c.id, code: c.code, name: c.name };
  courseModalOpen.value = true;
}

async function saveCourse() {
  if (!courseForm.value.code.trim() || !courseForm.value.name.trim()) {
    showAlert("Subject Code dan Subject Name wajib isi", "Validation");
    return;
  }

  try {
    if (courseForm.value.id) {
      await api.put(`/api/courses/${courseForm.value.id}`, {
        code: courseForm.value.code,
        name: courseForm.value.name,
      });
    } else {
      const r = await api.post("/api/courses", {
        code: courseForm.value.code,
        name: courseForm.value.name,
      });
      selectedCourseId.value = r.data.id;
    }

    courseModalOpen.value = false;
    await reloadAll();
  } catch (e) {
    showAlert("Save subject failed: " + (e?.response?.data || e.message), "Save Subject Failed");
  }
}

function removeCourse(c) {
  courseToDelete.value = c;
  deleteCourseModalOpen.value = true;
}

function closeDeleteCourseConfirm() {
  deleteCourseModalOpen.value = false;
  courseToDelete.value = null;
}

async function confirmRemoveCourse() {
  if (!courseToDelete.value) return;
  try {
    await api.delete(`/api/courses/${courseToDelete.value.id}`);
    if (selectedCourseId.value === courseToDelete.value.id) selectedCourseId.value = null;
    closeDeleteCourseConfirm();
    await reloadAll();
  } catch (e) {
    showAlert("Delete subject failed: " + (e?.response?.data || e.message), "Delete Subject Failed");
  }
}

async function selectCourse(id) {
  selectedCourseId.value = id;
  await loadTasks();
}

/* =======================
   TASK CRUD
======================= */
const taskModalOpen = ref(false);
const taskForm = ref({
  id: null,
  title: "",
  category: "ASSIGNMENT",
  due_at_local: "",
  notes: "",
  status: "PENDING",
});
const deleteTaskModalOpen = ref(false);
const taskToDelete = ref(null);
const toggleDoneModalOpen = ref(false);
const taskToToggleDone = ref(null);
const toggleDoneNextStatus = ref("PENDING");
const noteModalOpen = ref(false);
const noteViewTask = ref(null);

function openTaskCreate() {
  if (!selectedCourseId.value) {
    showAlert("Sila add/select subject dulu.", "Validation");
    return;
  }
  taskForm.value = { id: null, title: "", category: "ASSIGNMENT", due_at_local: "", notes: "", status: "PENDING" };
  taskModalOpen.value = true;
}

function openTaskEdit(t) {
  const d = new Date(t.due_at);
  const local = new Date(d.getTime() - d.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
  taskForm.value = {
    id: t.id,
    title: t.title,
    category: t.category,
    due_at_local: local,
    notes: t.notes || "",
    status: t.status,
  };
  taskModalOpen.value = true;
}

function toIsoSeconds(localDT) {
  return localDT + ":00";
}

async function saveTask() {
  if (!selectedCourseId.value) {
    showAlert("Sila select subject dulu.", "Validation");
    return;
  }
  if (!taskForm.value.title.trim() || !taskForm.value.due_at_local) {
    showAlert("Title dan Due At wajib isi", "Validation");
    return;
  }

  const payloadBase = {
    course_id: selectedCourseId.value,
    title: taskForm.value.title,
    category: taskForm.value.category,
    due_at: toIsoSeconds(taskForm.value.due_at_local),
    notes: taskForm.value.notes || null,
  };

  try {
    if (taskForm.value.id) {
      await api.put(`/api/tasks/${taskForm.value.id}`, {
        ...payloadBase,
        status: taskForm.value.status,
      });
    } else {
      await api.post("/api/tasks", payloadBase);
    }

    taskModalOpen.value = false;
    await Promise.all([loadTasks(), loadAllTasksForCourseOrder()]);
  } catch (e) {
    showAlert("Save task failed: " + (e?.response?.data || e.message), "Save Task Failed");
  }
}

function openToggleDoneConfirm(t) {
  taskToToggleDone.value = t;
  toggleDoneNextStatus.value = t.status === "DONE" ? "PENDING" : "DONE";
  toggleDoneModalOpen.value = true;
}

function closeToggleDoneConfirm() {
  toggleDoneModalOpen.value = false;
  taskToToggleDone.value = null;
  toggleDoneNextStatus.value = "PENDING";
}

async function confirmToggleDone() {
  if (!taskToToggleDone.value) return;
  await api.patch(`/api/tasks/${taskToToggleDone.value.id}/status`, { status: toggleDoneNextStatus.value });
  closeToggleDoneConfirm();
  await Promise.all([loadTasks(), loadAllTasksForCourseOrder()]);
}

function removeTask(t) {
  taskToDelete.value = t;
  deleteTaskModalOpen.value = true;
}

function closeDeleteTaskConfirm() {
  deleteTaskModalOpen.value = false;
  taskToDelete.value = null;
}

async function confirmRemoveTask() {
  if (!taskToDelete.value) return;
  await api.delete(`/api/tasks/${taskToDelete.value.id}`);
  closeDeleteTaskConfirm();
  await Promise.all([loadTasks(), loadAllTasksForCourseOrder()]);
}

function hasNote(t) {
  return String(t?.notes || "").trim().length > 0;
}

function openNoteView(t) {
  if (!hasNote(t)) return;
  noteViewTask.value = t;
  noteModalOpen.value = true;
}

function closeNoteView() {
  noteModalOpen.value = false;
  noteViewTask.value = null;
}

/* =======================
   UI helpers
======================= */
function fmtDateDate(dt) {
  const d = dt instanceof Date ? dt : new Date(dt);
  if (Number.isNaN(d.getTime())) return "-";
  return d.toLocaleDateString("en-GB", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
  });
}

function fmtDateTime(dt) {
  const d = dt instanceof Date ? dt : new Date(dt);
  if (Number.isNaN(d.getTime())) return "-";
  return d.toLocaleTimeString("en-US", {
    hour: "2-digit",
    minute: "2-digit",
    second: "2-digit",
    hour12: true,
  }).toLowerCase();
}

function leftText(t) {
  const diffMs = new Date(t.due_at) - now.value;
  const mins = Math.floor(diffMs / 60000);
  const abs = Math.abs(mins);
  const d = Math.floor(abs / (60 * 24));
  const h = Math.floor((abs % (60 * 24)) / 60);
  const m = abs % 60;
  if (t.status === "DONE") return "Completed";
  if (mins < 0) return `Overdue ${d}d ${h}h ${m}m`;
  return `${d}d ${h}h ${m}m`;
}

function leftClassBs(t) {
  if (t.status === "DONE") return "text-muted";
  const diffMs = new Date(t.due_at) - now.value;
  if (diffMs < 0) return "text-danger";
  if (diffMs <= 24 * 3600 * 1000) return "text-warning";
  return "text-success";
}

function isDueSoonTask(t) {
  const status = String(t.status || "").toUpperCase();
  if (status === "DONE") return false;
  const dueAt = new Date(t.due_at);
  if (Number.isNaN(dueAt.getTime())) return false;
  const diffMs = dueAt - now.value;
  return diffMs >= 0 && diffMs < 7 * 24 * 3600 * 1000;
}

function formatBadgeCount(n) {
  if (n > 99) return "99+";
  return String(n);
}

/* initial load */
onMounted(async () => {
  document.addEventListener("click", handleGlobalClick);
  loadUiPrefsFromCookies();
  await initAuth();
});

onBeforeUnmount(() => {
  document.removeEventListener("click", handleGlobalClick);
});

watch(isViewMode, v => {
  setCookie(VIEW_MODE_COOKIE, String(v));
});

watch(
  viewCards,
  v => {
    setCookie(VIEW_CARDS_COOKIE, JSON.stringify(v));
  },
  { deep: true }
);

watch(selectedCourseId, v => {
  if (v === null || v === undefined) {
    clearCookie(SELECTED_COURSE_COOKIE);
    return;
  }
  setCookie(SELECTED_COURSE_COOKIE, String(v));
});
</script>

<style scoped>
.upcoming-badge {
  --bs-badge-font-size: 0.62em;
  font-size: 0.6rem !important;
  padding: 0.16rem 0.34rem;
}

.upcoming-left-text {
  font-size: 0.72rem !important;
}

.due-soon-square {
  position: relative;
  display: inline-flex;
  width: 1.15rem;
  height: 1.15rem;
  align-items: center;
  justify-content: center;
  vertical-align: middle;
}

.due-soon-square > i {
  font-size: 1.15rem;
  line-height: 1;
}

.due-soon-square-count {
  position: absolute;
  color: #fff;
  font-size: 0.56rem;
  line-height: 1;
  font-weight: 700;
}

.upcoming-alert {
  border-color: #dc3545;
  background-color: #fff5f5;
}

.upcoming-alert .card-header {
  background-color: #dc3545 !important;
  color: #fff;
}

.upcoming-alert .card-header .text-primary,
.upcoming-alert .card-header .text-muted {
  color: #fff !important;
}

.upcoming-alert .card-body {
  background-color: transparent;
}

.upcoming-alert .list-group-item {
  background-color: transparent !important;
}

.due-soon-row > td {
  background-color: #fff1f1 !important;
}

.subject-list-item {
  cursor: pointer;
}

.subject-list-item.active {
  background-color: #0d6efd !important;
  border-color: #0d6efd !important;
  color: #fff !important;
}

.subject-list-item.active .small,
.subject-list-item.active .fw-semibold,
.subject-list-item.active span {
  color: #fff !important;
}

.table .due-table-head th,
.due-table-head th {
  --bs-table-bg: #0d6efd !important;
  --bs-table-color: #fff !important;
  background-color: #0d6efd !important;
  color: #fff !important;
  box-shadow: inset 0 0 0 9999px #0d6efd !important;
}

.due-dates-title {
  font-size: 1rem !important;
  line-height: 1.2;
  font-weight: 700 !important;
}

.profile-menu {
  min-width: 220px;
  z-index: 1050;
}

.captcha-image {
  display: block;
  flex: 0 0 170px;
  width: 170px;
  height: 38px;
  object-fit: fill;
  background: #f8fafc;
}

.captcha-row {
  align-items: stretch;
}

.captcha-input {
  height: 38px;
}

.auth-switch-btn.btn-outline-primary:hover,
.auth-switch-btn.btn-outline-primary:focus {
  color: #0b3b94;
  background-color: #dbeafe;
  border-color: #93c5fd;
}

.auth-switch-btn.btn-primary.active {
  background-color: #0d6efd !important;
  border-color: #0d6efd !important;
}

.btn-primary {
  background-color: #0d6efd !important;
  border-color: #0d6efd !important;
}

.btn-primary:hover,
.btn-primary:focus,
.btn-primary:active {
  background-color: #0b5ed7 !important;
  border-color: #0b5ed7 !important;
}

.bg-primary {
  background-color: #0d6efd !important;
}

.auth-landing-title,
.auth-landing-title * {
  color: #000 !important;
}

.brand-text-black {
  color: #4b5563 !important;
}

.brand-top {
  font-size: 1.5rem !important;
  line-height: 1.1;
}

.brand-logo {
  width: 352px;
  height: auto;
}

.brand-logo-nav {
  width: 160px;
}
</style>
