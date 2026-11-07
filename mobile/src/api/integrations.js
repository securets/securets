import API from './client';

export const integrationApi = {
    // Get all integrations for the logged-in user
    list: () => API.get('/integrations'),

    // Add a new integration
    add: (name, url) => API.post('/integrations', { name, url }),

    // Update an integration's details
    update: (id, data) => API.patch(`/integrations/${id}`, data),

    // Delete an integration
    remove: (id) => API.delete(`/integrations/${id}`),

    // Toggle active/inactive
    toggle: (id) => API.patch(`/integrations/${id}/toggle`),

    // Get current TOTP code
    totp: (id) => API.get(`/integrations/${id}/totp`),

    // Approve or decline a login event
    respond: (eventId, decision) =>
        API.patch(`/login-events/${eventId}/respond`, { decision }),

    // Get all login events (history)
    history: () => API.get('/login-events'),

    // Archive a login event
    archive: (eventId) => API.patch(`/login-events/${eventId}/archive`),

    // Delete a login event
    delete: (eventId) => API.delete(`/login-events/${eventId}`),

    // Get pending approvals
    pending: () => API.get('/login-events/pending'),

    // Register Expo push token
    registerDevice: (expoPushToken, deviceName) =>
        API.post('/devices', { expo_push_token: expoPushToken, device_name: deviceName }),

    // List registered devices
    devices: () => API.get('/devices'),

    // Remove a device
    removeDevice: (id) => API.delete(`/devices/${id}`),
};
