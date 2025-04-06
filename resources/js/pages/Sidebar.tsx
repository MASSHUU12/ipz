import React from "react";
import { Drawer, List, ListItem, ListItemButton, ListItemIcon, ListItemText } from "@mui/material";
import { Home, Person, Leaderboard, Message, Settings, Star, Logout } from "@mui/icons-material";
import { useState } from "react";

const menuItems = [
  { text: "Dashboard", icon: <Home />, path: "/" },
  { text: "Profile", icon: <Person />, path: "/profile" },
  { text: "Leaderboard", icon: <Leaderboard />, path: "/leaderboard" },
  { text: "Message", icon: <Message />, path: "/message" },
  { text: "Settings", icon: <Settings />, path: "/settings" },
  { text: "Favourite", icon: <Star />, path: "/favourite" },
  { text: "Signout", icon: <Logout />, path: "/signout" },
];

const Sidebar = () => {
  const [selected, setSelected] = useState("Dashboard");

  return (
    <Drawer variant="permanent" sx={{ width: 240, flexShrink: 0, "& .MuiDrawer-paper": { width: 240, backgroundColor: "#1e1e1e", color: "#fff" } }}>
      <List>
        {menuItems.map((item) => (
          <ListItem key={item.text} disablePadding>
            <ListItemButton
              selected={selected === item.text}
              onClick={() => setSelected(item.text)}
              sx={{
                borderRadius: "10px",
                margin: "5px",
                "&.Mui-selected": {
                  backgroundColor: "#A8E6CF",
                  color: "#000",
                  "& .MuiListItemIcon-root": { color: "#000" },
                },
              }}
            >
              <ListItemIcon sx={{ color: "#fff" }}>{item.icon}</ListItemIcon>
              <ListItemText primary={item.text} />
            </ListItemButton>
          </ListItem>
        ))}
      </List>
    </Drawer>
  );
};

export default Sidebar;
