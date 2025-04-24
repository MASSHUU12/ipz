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
} from "@mui/icons-material";
import { Link } from "@inertiajs/react";

// Menu definicja
const menuItems = [
  { text: "Dashboard", icon: <Home />, path: "/dashboard" },
  { text: "Profile", icon: <Person />, path: "/profile" },
  { text: "Leaderboard", icon: <Leaderboard />, path: "/air-quality-ranking" },
  { text: "Message", icon: <Message />, path: "/message" },
  { text: "Settings", icon: <Settings />, path: "/settings" },
  { text: "Favourite", icon: <Star />, path: "/favourite" },
  { text: "Signout", icon: <Logout />, path: "/signout" },
];

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
                component={Link}
                href={item.path}
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
