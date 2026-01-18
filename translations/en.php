<?php

return [
    // Greeting
    'greeting' => "Hello! 👋\n\nI'll help you create a website for your business. What task would you like to solve?",
    
    // Task definition
    'task_definition_question' => "Tell me, what task would you like to solve? Describe your project.",
    'task_definition_response' => "Got it! You want: {task_description}\n\nPlease clarify:\n• What is your budget?\n• When do you need to launch?\n• Are there any special requirements?",
    
    // Clarification
    'clarification_services' => "Great! Based on your requirements, I suggest considering the following options:\n\nChoose a service category:",
    'clarification_no_services' => "Thank you for the information! To discuss the details of your project, please contact our manager:\n\n👤 @Branch",
    'category_services' => "Services in this category:\n\nChoose a suitable service:",
    'category_no_services' => "There are no services in this category yet. Choose another category or contact a manager:\n\n👤 @Branch",
    
    // Service selection
    'service_selected' => "Great choice! {service_name}\n\n💰 Price: {price_from} - {price_to} ₽\n\n{description}\n\nReady to discuss details?",
    'service_fallback' => "Got it! Let's discuss the project cost. What is your approximate budget?",
    
    // Price range
    'price_range' => "Got your budget! 💰\n\nTo prepare an accurate proposal, I need your contacts. Can you leave your phone number or email?",
    
    // Call to action
    'call_to_action' => "Great! To contact you, please leave your contact details:\n\n• Phone\n• Or email",
    
    // Contact collection
    'contact_invalid' => "Please provide a valid phone number or email for contact.",
    'contact_success' => "Thank you! ✅\n\nYour application has been accepted. Our manager will contact you soon.\n\nApplication number: #{lead_id}",
    'contact_group_note' => "💡 In groups/channels, you can provide your phone number as text in a message.",
    
    // Buttons
    'button_start' => '🚀 Start',
    'button_phone' => '📱 Leave phone',
    'button_email' => '✉️ Leave email',
    'button_back_categories' => '⬅️ Back to categories',
    
    // Price
    'price_from' => 'from',
    
    // Contact
    'contact_received' => 'Contact: {phone}',
    
    // Admin
    'admin_dashboard' => 'Dashboard',
    'admin_dialogs' => 'Dialogs',
    'admin_leads' => 'Leads',
    'admin_services' => 'Services',
    'admin_users' => 'Users',
    'admin_search' => 'Search',
    'admin_logout' => 'Logout',
];
