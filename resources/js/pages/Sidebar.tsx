import React from "react";
import {
  Drawer,
  List,
  ListItem,
  ListItemButton,
  ListItemIcon,
  ListItemText,
} from "@mui/material";
import {
  Home,
  Person,
  Leaderboard,
  Message,
  Settings,
  Star,
  Logout,
  PersonAdd,
} from "@mui/icons-material";
import { Link } from "@inertiajs/react";
import Register from "./register";

const isAuthenticated = !!localStorage.getItem('authToken');

const menuItems = isAuthenticated ?
[
  { text: "Dashboard", icon: <Home />, path: "/dashboard" },
  { text: "Profile", icon: <Person />, path: "/profile" },
  { text: "Leaderboard", icon: <Leaderboard />, path: "/air-quality-ranking" },
  { text: "Message", icon: <Message />, path: "/message" },
  { text: "Settings", icon: <Settings />, path: "/settings" },
  { text: "Favourite", icon: <Star />, path: "/favourite" },
  {
    text: "Signout",
    icon: <Logout />,
    action: () => {
      localStorage.removeItem("authToken");
      window.location.href = "/login";
    },
  },
]:
[{ text: "Dashboard", icon: <Home />, path: "/dashboard" },
  { text: "Leaderboard", icon: <Leaderboard />, path: "/air-quality-ranking" },
  { text: "Register", icon: <PersonAdd />, path: "/register" },
]

const Sidebar = () => {
  // Aktualna ścieżka URL (np. "/profile")
  const currentPath = window.location.pathname;

  return (
    <Drawer
      variant="permanent"
      sx={{
        width: 240,
        flexShrink: 0,
        "& .MuiDrawer-paper": {
          width: 240,
          backgroundColor: "#1e1e1e",
          color: "#fff",
          borderRight: "none",
        },
      }}
    >
      <List>
        {menuItems.map((item) => {
          const isSelected = currentPath === item.path;

          return (
            <ListItem key={item.text} disablePadding>
              <ListItemButton
                component={item.path ? Link : "button"}
                href={item.path}
                onClick={item.action}
                selected={isSelected}
                sx={{
                  borderRadius: "10px",
                  margin: "5px",
                  "&.Mui-selected": {
                    backgroundColor: "#00c8ff33",
                    color: "#00c8ff",
                    "& .MuiListItemIcon-root": { color: "#00c8ff" },
                  },
                  "&:hover": {
                    backgroundColor: "#00c8ff22",
                  },
                }}
              >
                <ListItemIcon sx={{ color: isSelected ? "#00c8ff" : "#fff" }}>
                  {item.icon}
                </ListItemIcon>
                <ListItemText primary={item.text} />
              </ListItemButton>
            </ListItem>
          );
        })}
      </List>
    </Drawer>
  );
};

export default Sidebar;
