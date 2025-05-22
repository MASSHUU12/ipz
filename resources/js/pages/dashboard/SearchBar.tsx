import React, { useRef } from "react";
import {
  AppBar,
  Toolbar,
  TextField,
  InputAdornment,
  IconButton,
  useMediaQuery,
  styled,
  Autocomplete,
  CircularProgress,
  Popper,
} from "@mui/material";
import {
  Menu as MenuIcon,
  Search as SearchIcon,
  Notifications,
} from "@mui/icons-material";
import { useAddressSuggestions } from "@/hooks/useAddressSuggestions";

interface Props {
  searchValue: string;
  onSearchChange: (v: string) => void;
  onSearchSubmit: () => void;
  onMenuClick?: () => void;
}

const StyledAppBar = styled(AppBar)({
  backgroundColor: "#1e1e1e",
});

const StyledTextField = styled(TextField)({
  backgroundColor: "#2e2e2e",
  borderRadius: 4,
  "& .MuiInputBase-input": {
    color: "#fff",
  },
  "& .MuiOutlinedInput-notchedOutline": {
    borderColor: "transparent",
  },
});

export const SearchBar: React.FC<Props> = ({
  searchValue,
  onSearchChange,
  onSearchSubmit,
  onMenuClick,
}) => {
  const isMobile = useMediaQuery("(max-width:900px)");
  const { options, loading } = useAddressSuggestions(searchValue);

  const inputRef = useRef<HTMLInputElement>(null);

  const handleSubmit = () => {
    inputRef.current?.blur();
    onSearchSubmit();
  };

  return (
    <StyledAppBar position="static">
      <Toolbar sx={{ justifyContent: "space-between" }}>
        {isMobile && (
          <IconButton
            edge="start"
            color="inherit"
            onClick={onMenuClick}
            sx={{ mr: 1 }}>
            <MenuIcon />
          </IconButton>
        )}

        <Autocomplete
          freeSolo
          disableClearable
          fullWidth
          options={options}
          loading={loading}
          inputValue={searchValue}
          onInputChange={(_, v, reason) =>
            reason === "input" && onSearchChange(v)
          }
          onChange={(_, v) => typeof v === "string" && onSearchChange(v)}
          onKeyDown={e => e.key === "Enter" && handleSubmit()}
          PopperComponent={props => (
            <Popper {...props} style={{ zIndex: 1300 }} />
          )}
          renderInput={params => (
            <StyledTextField
              {...params}
              placeholder="Search city"
              size="small"
              variant="outlined"
              inputRef={inputRef}
              InputProps={{
                ...params.InputProps,
                endAdornment: (
                  <>
                    {loading ? (
                      <CircularProgress
                        size={20}
                        sx={{ color: "#fff", mr: 1 }}
                      />
                    ) : null}
                    <InputAdornment position="end">
                      <IconButton onClick={handleSubmit} sx={{ color: "#fff" }}>
                        <SearchIcon />
                      </IconButton>
                    </InputAdornment>
                  </>
                ),
              }}
            />
          )}
        />

        <IconButton sx={{ color: "#fff" }}>
          <Notifications />
        </IconButton>
      </Toolbar>
    </StyledAppBar>
  );
};
