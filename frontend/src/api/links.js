import api from './axios';

export const getLinks = (params = {}) => {
    return api.get("/links", { params });
};

export const createLink = (data) => {
    return api.post("/links", data);
};

export const deleteLink = (id) => {
    return api.delete(`/links/${id}`);
};